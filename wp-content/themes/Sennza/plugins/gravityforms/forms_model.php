<?php

require_once(ABSPATH . WPINC . "/post.php");

define("GFORMS_MAX_FIELD_LENGTH", 200);

class GFFormsModel {

    public static $uploaded_files = array();
    public static $unique_ids = array();

    private static $_confirmations = array();
    private static $_current_forms = array();
    private static $_current_lead = null;

    public static function flush_current_forms(){
        self::$_current_forms = null;
    }

    public static function flush_current_lead(){
        self::$_current_lead = null;
    }

    public static function flush_confirmations(){
        self::$_confirmations = null;
    }

    public static function get_form_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_form";
    }

    public static function get_meta_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_form_meta";
    }

    public static function get_form_view_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_form_view";
    }

    public static function get_lead_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead";
    }

    public static function get_lead_meta_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_meta";
    }

    public static function get_lead_notes_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_notes";
    }

    public static function get_lead_details_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_detail";
    }

    public static function get_lead_details_long_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_detail_long";
    }

    public static function get_lead_view_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_view";
    }

    public static function get_forms($is_active = null, $sort_column = "title", $sort_dir = "ASC", $is_trash = false){
        global $wpdb;
        $form_table_name =  self::get_form_table_name();
        $lead_table_name = self::get_lead_table_name();
        $view_table_name = self::get_form_view_table_name();

        $where_arr = array();
        $where_arr[] = $wpdb->prepare("is_trash=%d", $is_trash);
        if($is_active !== null)
            $where_arr[] = $wpdb->prepare("is_active=%d", $is_active);

        $where_clause = "WHERE " . join(" AND ", $where_arr);
        $sort_keyword = $sort_dir == "ASC" ? "ASC" : "DESC";
        $sort_column = ESC_SQL($sort_column);
        $order_by = !empty($sort_column) ? "ORDER BY $sort_column $sort_keyword" : "";

        $sql = "SELECT f.id, f.title, f.date_created, f.is_active, 0 as lead_count, 0 view_count
                FROM $form_table_name f
                $where_clause
                $order_by";

        //Getting all forms
        $forms = $wpdb->get_results($sql);

        //Getting entry count per form
        $sql = "SELECT form_id, count(id) as lead_count FROM $lead_table_name l WHERE status='active' GROUP BY form_id";
        $entry_count = $wpdb->get_results($sql);

        //Getting view count per form
        $sql = "SELECT form_id, sum(count) as view_count FROM $view_table_name GROUP BY form_id";
        $view_count = $wpdb->get_results($sql);

        //Adding entry counts and to form array
        foreach($forms as &$form){
            foreach($view_count as $count){
                if($count->form_id == $form->id){
                    $form->view_count = $count->view_count;
                    break;
                }
            }

            foreach($entry_count as $count){
                if($count->form_id == $form->id){
                    $form->lead_count = $count->lead_count;
                    break;
                }
            }
        }

        return $forms;
    }

    public static function get_forms_by_id($ids){
        _deprecated_function('get_forms_by_id', '1.7', 'get_form_meta_by_id');
        return self::get_form_meta_by_id($ids);
    }

    public static function get_form_payment_totals($form_id){
        global $wpdb;
        $lead_table_name = self::get_lead_table_name();

        $sql = $wpdb->prepare(" SELECT sum(payment_amount) revenue, count(l.id) orders
                                 FROM $lead_table_name l
                                 WHERE form_id=%d AND payment_amount IS NOT null", $form_id);

        $totals = $wpdb->get_row($sql, ARRAY_A);

        $active = $wpdb->get_var($wpdb->prepare(" SELECT count(id) as active
                                                 FROM $lead_table_name
                                                 WHERE form_id=%d AND payment_status='Active'", $form_id));

        if(empty($active))
            $active = 0;

        $totals["active"] = $active;

        return $totals;
    }

    public static function get_form_counts($form_id){
        global $wpdb;
        $lead_table_name = self::get_lead_table_name();
        $sql = $wpdb->prepare(
                "SELECT
                    (SELECT count(0) FROM $lead_table_name WHERE form_id=%d AND status='active') as total,
                    (SELECT count(0) FROM $lead_table_name WHERE is_read=0 AND status='active' AND form_id=%d) as unread,
                    (SELECT count(0) FROM $lead_table_name WHERE is_starred=1 AND status='active' AND form_id=%d) as starred,
                    (SELECT count(0) FROM $lead_table_name WHERE status='spam' AND form_id=%d) as spam,
                    (SELECT count(0) FROM $lead_table_name WHERE status='trash' AND form_id=%d) as trash",
                    $form_id, $form_id, $form_id, $form_id, $form_id);

         $results = $wpdb->get_results($sql, ARRAY_A);

         return $results[0];

    }

    public static function get_form_summary(){
        global $wpdb;
        $form_table_name =  self::get_form_table_name();
        $lead_table_name = self::get_lead_table_name();

        $sql = "SELECT l.form_id, count(l.id) as unread_count
                FROM $lead_table_name l
                WHERE is_read=0 AND status='active'
                GROUP BY form_id";

        //getting number of unread and total leads for all forms
        $unread_results = $wpdb->get_results($sql, ARRAY_A);

        $sql = "SELECT l.form_id, max(l.date_created) as last_lead_date, count(l.id) as total_leads
                FROM $lead_table_name l
                WHERE status='active'
                GROUP BY form_id";

        $lead_date_results = $wpdb->get_results($sql, ARRAY_A);

        $sql = "SELECT id, title, '' as last_lead_date, 0 as unread_count
                FROM $form_table_name
                WHERE is_active=1
                ORDER BY title";

        $forms = $wpdb->get_results($sql, ARRAY_A);


        for($i=0; $count = sizeof($forms), $i<$count; $i++){
            if(is_array($unread_results)){
                foreach($unread_results as $unread_result){
                    if($unread_result["form_id"] == $forms[$i]["id"]){
                        $forms[$i]["unread_count"] = $unread_result["unread_count"];
                        break;
                    }
                }
            }

            if(is_array($lead_date_results)){
                foreach($lead_date_results as $lead_date_result){
                    if($lead_date_result["form_id"] == $forms[$i]["id"]){
                        $forms[$i]["last_lead_date"] = $lead_date_result["last_lead_date"];
                        $forms[$i]["total_leads"] = $lead_date_result["total_leads"];
                        break;
                    }
                }
            }

        }

        return $forms;
    }

    public static function get_form_count(){
        global $wpdb;
        $form_table_name =  self::get_form_table_name();
        $results = $wpdb->get_results("
            SELECT
            (SELECT count(0) FROM $form_table_name WHERE is_trash = 0) as total,
            (SELECT count(0) FROM $form_table_name WHERE is_active=1 AND is_trash = 0 ) as active,
            (SELECT count(0) FROM $form_table_name WHERE is_active=0 AND is_trash = 0 ) as inactive,
            (SELECT count(0) FROM $form_table_name WHERE is_trash=1) as trash
            ");

        return array(   "total" => intval($results[0]->total),
                        "active" => intval($results[0]->active),
                        "inactive" => intval($results[0]->inactive),
                        "trash" => intval($results[0]->trash)
                        );
    }

    public static function get_form_id($form_title){
        $forms = self::get_forms();
        foreach($forms as $form){
            $sanitized_name = str_replace("[", "", str_replace("]","", $form->title));
            if($form->title == $form_title || $sanitized_name == $form_title)
                return $form->id;
        }
        return 0;
    }

    public static function get_form($form_id, $allow_trash = false){
        global $wpdb;
        $table_name =  self::get_form_table_name();
        $trash_clause = $allow_trash ? "" : "AND is_trash = 0";
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%d {$trash_clause}", $form_id));
        return isset($results[0]) ? $results[0] : false;
    }

    public static function unserialize($string){

        if(is_serialized($string)){
            $obj = @unserialize($string);
        }
        else{
            $obj = json_decode($string, true);
        }

        return $obj;
    }

    public static function get_form_meta($form_id){
        global $wpdb;

        // return cached version if form meta has been previously retrieved for this form
        if(isset(self::$_current_forms[$form_id])){
            return self::$_current_forms[$form_id];
		}

        $table_name =  self::get_meta_table_name();
        $form_row = $wpdb->get_row($wpdb->prepare("SELECT display_meta, notifications FROM {$table_name} WHERE form_id=%d", $form_id), ARRAY_A);


        //Loading main form object (supports serialized strings as well as JSON strings)
        $form = self::unserialize($form_row["display_meta"]);

        if(!$form)
            return null;

        //loading notifications
        $form["notifications"] = self::unserialize($form_row["notifications"]);


        //copying some form variables down to fields for easier access
        $page_number = 1;
        $description_placement = rgar($form, "descriptionPlacement") == "above" ? "above" : "below";
        if(is_array(rgar($form,"fields"))){
            foreach($form["fields"] as &$field){
                $field["label"] = !isset($field["label"]) ? "" : $field["label"];
                $field["formId"] = $form["id"];
                $field["pageNumber"] = $page_number;
                $field["descriptionPlacement"] = $description_placement;
                if($field["type"] == "page"){
                    $page_number++;
                    $field["pageNumber"] = $page_number;
                }
            }
        }

        // loading confirmations from legacy structure into new structure
        $form = self::load_confirmations($form);

         //only migrate legacy notification if there isn't any notification configured in new structure
        if(!isset($form["notifications"])){
            $form = self::load_notifications_from_legacy($form); //moving notification data from legacy structure into new "notifications" array
        }

        //load notifications to legacy structure to maintain backward compatibility with legacy hooks and functions
        $form = self::load_notifications_to_legacy($form);

        // cached form meta for cheaper retrieval on subsequent requests
        self::$_current_forms[$form_id] = $form;

        return $form;
    }

    public static function get_form_meta_by_id($ids){
        global $wpdb;
        $form_table_name =  self::get_form_table_name();
        $meta_table_name =  self::get_meta_table_name();

        if(is_array($ids))
            $ids = implode(",", array_map('intval', $ids ) );
        else
            $ids = intval($ids);

        $results = $wpdb->get_results(" SELECT display_meta, confirmations, notifications FROM {$form_table_name} f
                                        INNER JOIN {$meta_table_name} m ON f.id = m.form_id
                                        WHERE id in({$ids})", ARRAY_A);

        foreach ($results as &$result) {
            $form = self::unserialize($result["display_meta"]);
            $form['confirmations'] = self::unserialize($result["confirmations"]);
            $form['notifications'] = self::unserialize($result["notifications"]);
            $result = $form;
        }

        return $results;

    }

    private static function load_notifications_to_legacy($form){
        if(!is_array(rgar($form, "notifications")))
            return $form;

        foreach($form["notifications"] as $notification){
            if(!in_array(rgar($notification,"type"), array("user", "admin")))
                continue;

            $legacy_notification = $notification;

            if($notification["toType"] == "field"){
                $legacy_notification["toField"] = $notification["to"];
                unset($legacy_notification["to"]);
            }

            //unsetting new properties
            unset($legacy_notification["toType"]);
            unset($legacy_notification["id"]);
            unset($legacy_notification["event"]);
            unset($legacy_notification["name"]);
            if(isset($legacy_notification["type"]))
                unset($legacy_notification["type"]);

            //saving into form object
            $property = $notification["type"] == "user" ? "autoResponder" : "notification";
            $form[$property] = $legacy_notification;
        }

        return $form;
    }

    private static function load_notifications_from_legacy($form){

        $form["notifications"] = array();
        if(GFCommon::has_admin_notification($form)){
            $admin_notification = $form["notification"];

            //if there is a fromField configured, move it to "from" as a merge tag
            $admin_notification = self::convert_property_to_merge_tag($form, $form["notification"], "from", "fromField");

            //if there is a fromNameField configured, move it to "fromName" as a merge tag
            $admin_notification = self::convert_property_to_merge_tag($form, $form["notification"], "fromName", "fromNameField");

            //if there is a replyToField configured, move it to "replyTo" as a merge tag
            $admin_notification = self::convert_property_to_merge_tag($form, $form["notification"], "replyTo", "replyToField");

            //if routing is configured, set toType to routing, otherwise, set it to email
            $admin_notification["toType"] = !rgempty("routing", $admin_notification) ? "routing" : "email";

            $notification_id = uniqid();

            //assigning this notification to the form_submission action
            $admin_notification["event"] = "form_submission";
            $admin_notification["name"] = __("Admin Notification", "gravityforms");
            $admin_notification["type"] = "admin";
            $admin_notification["id"] = $notification_id;

            //copying admin notification as an item in the new notifications array
            $form["notifications"][$notification_id] = $admin_notification;
        }

        if(GFCommon::has_user_notification($form)){

            $user_notification = $form["autoResponder"];

            //if there is a toField configured, set toType to field, if not, set it toemail
            $to_field = rgar($user_notification, "toField");
            if(!empty($to_field)){
                $user_notification["toType"] = "field";
                $user_notification["to"] = $to_field;
            }
            else{
                $user_notification["toType"] = "email";
            }

            $notification_id = uniqid();
            //assigning this notification to the form_submission action
            $user_notification["event"] = "form_submission";
            $user_notification["name"] = __("User Notification", "gravityforms");
            $user_notification["type"] = "user";
            $user_notification["id"] = $notification_id;

            //copying user notification as an item in the new notifications array
            $form["notifications"][$notification_id] = $user_notification;
        }

        self::save_form_notifications($form["id"], $form["notifications"]);

        return $form;
    }

    private static function convert_property_to_merge_tag($form, $array, $target_property, $source_property){
        $merge_tag = self::get_field_merge_tag($form, rgar($array, $source_property));
            if($merge_tag){
                $array[$target_property] = $merge_tag;
                unset($array[$source_property]);
            }

        return $array;
    }

    private static function get_field_merge_tag($form, $field_id){
        $field = self::get_field($form, $field_id);
        if(!$field)
            return false;

        return "{" . GFCommon::get_label($field, $field_id) . ":" . $field_id . "}";
    }

    public static function add_default_properties($form){
        if(is_array(rgar($form,"fields"))){
            $all_fields = array("adminLabel"=>"","adminOnly"=>"","allowsPrepopulate"=>"","defaultValue"=>"","description"=>"","content"=>"","cssClass"=>"",
                                "errorMessage"=>"","id"=>"","inputName"=>"","isRequired"=>"","label"=>"","noDuplicates"=>"",
                                "size"=>"","type"=>"","postCustomFieldName"=>"","displayAllCategories"=>"","displayCaption"=>"","displayDescription"=>"",
                                "displayTitle"=>"","inputType"=>"","rangeMin"=>"","rangeMax"=>"","calendarIconType"=>"",
                                "calendarIconUrl"=>"", "dateType"=>"","dateFormat"=>"","phoneFormat"=>"","addressType"=>"","defaultCountry"=>"","defaultProvince"=>"",
                                "defaultState"=>"","hideAddress2"=>"","hideCountry"=>"","hideState"=>"","inputs"=>"","nameFormat"=>"","allowedExtensions"=>"",
                                "captchaType"=>"","pageNumber"=>"","captchaTheme"=>"","simpleCaptchaSize"=>"","simpleCaptchaFontColor"=>"","simpleCaptchaBackgroundColor"=>"",
                                "failed_validation"=>"", "productField" => "", "enablePasswordInput" => "", "maxLength" => "", "enablePrice" => "", "basePrice" => "");

            foreach($form["fields"] as &$field)
                $field = wp_parse_args($field, $all_fields);
        }
        return $form;
    }

    public static function get_grid_column_meta($form_id){
        global $wpdb;

        $table_name =  self::get_meta_table_name();
        return maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT entries_grid_meta FROM $table_name WHERE form_id=%d", $form_id)));
    }

    public static function update_grid_column_meta($form_id, $columns){
        global $wpdb;

        $table_name = self::get_meta_table_name();
        $meta = maybe_serialize(stripslashes_deep($columns) );
        $wpdb->query( $wpdb->prepare("UPDATE $table_name SET entries_grid_meta=%s WHERE form_id=%d", $meta, $form_id) );
    }

    public static function get_lead_detail_id($current_fields, $field_number){
        foreach($current_fields as $field)
            if($field->field_number == $field_number)
                return $field->id;

        return 0;
    }

    public static function update_form_active($form_id, $is_active){
        global $wpdb;
        $form_table = self::get_form_table_name();
        $sql = $wpdb->prepare("UPDATE $form_table SET is_active=%d WHERE id=%d", $is_active, $form_id);
        $wpdb->query($sql);
    }

    public static function update_notification_active($form_id, $notification_id, $is_active){
        $form = GFFormsModel::get_form_meta($form_id);

        if(!isset($form["notifications"][$notification_id]))
            return new WP_Error("not_found", __("Notification not found", "gravityforms"));

        $form["notifications"][$notification_id]["isActive"] = (bool) $is_active;

        $result = GFFormsModel::update_form_meta($form_id, $form["notifications"], "notifications");
        return $result;
    }

    public static function update_confirmation_active($form_id, $confirmation_id, $is_active){
        $form = GFFormsModel::get_form_meta($form_id);

        if(!isset($form["confirmations"][$confirmation_id]))
            return new WP_Error("not_found", __("Notification not found", "gravityforms"));

        $form["confirmations"][$confirmation_id]["isActive"] = (bool) $is_active;

        $result = GFFormsModel::update_form_meta($form_id, $form["confirmations"], "confirmations");
        return $result;
    }

    public static function update_forms_active($forms, $is_active){
        foreach($forms as $form_id)
            self::update_form_active($form_id, $is_active);
    }

    public static function update_leads_property($leads, $property_name, $property_value){
        foreach($leads as $lead)
            self::update_lead_property($lead, $property_name, $property_value);
    }

    public static function update_lead_property($lead_id, $property_name, $property_value, $update_akismet=true, $disable_hook=false){
        global $wpdb;
        $lead_table = self::get_lead_table_name();

        $lead = self::get_lead($lead_id);

        //marking entry as "spam" or "not spam" with Akismet if the plugin is installed
        if($update_akismet && GFCommon::akismet_enabled($lead["form_id"]) && $property_name == "status" && in_array($property_value, array("active", "spam"))){

            $current_status = $lead["status"];
            if($current_status == "spam" && $property_value == "active"){
                $form = self::get_form_meta($lead["form_id"]);
                GFCommon::mark_akismet_spam($form, $lead, false);
            }
            else if($current_status == "active" && $property_value == "spam"){
                $form = self::get_form_meta($lead["form_id"]);
                GFCommon::mark_akismet_spam($form, $lead, true);
            }
        }

        //updating lead
        $result = $wpdb->update($lead_table, array($property_name => $property_value ), array("id" => $lead_id));

        if(!$disable_hook){

            $previous_value = rgar($lead, $property_name);

            if($previous_value != $property_value) {

                // if property is status, prev value is spam and new value is active
                if($property_name == 'status' && $previous_value == 'spam' && $property_value == 'active' && !rgar($lead, 'post_id')) {
                    $lead[$property_name] = $property_value;
                    $lead['post_id'] = GFCommon::create_post($form, $lead);
                }

                do_action("gform_update_{$property_name}", $lead_id, $property_value, $previous_value);
            }
        }

        return $result;
    }

    public static function update_lead($lead){
        global $wpdb;
        $lead_table = self::get_lead_table_name();

        $payment_date = strtotime(rgar($lead,"payment_date")) ? "'" . gmdate( 'Y-m-d H:i:s', strtotime("{$lead["payment_date"]}") ) . "'" : "NULL";
        $payment_amount = !rgblank(rgar($lead, "payment_amount")) ? (float) rgar($lead, "payment_amount") : "NULL";
        $transaction_type = !rgempty("transaction_type", $lead) ? intval($lead["transaction_type"]) : "NULL";

        $status = !rgempty("status", $lead) ? $lead["status"] : "active";
        $source_url = self::truncate(rgar($lead,"source_url"), 200);
        $user_agent = self::truncate(rgar($lead,"user_agent"), 250);

        $sql = $wpdb->prepare("UPDATE $lead_table SET
                                    form_id=%d,
                                    post_id=%d,
                                    is_starred=%d,
                                    is_read=%d,
                                    ip=%s,
                                    source_url=%s,
                                    user_agent=%s,
                                    currency=%s,
                                    payment_status=%s,
                                    payment_date={$payment_date},
                                    payment_amount={$payment_amount},
                                    transaction_id=%s,
                                    is_fulfilled=%d,
                                    transaction_type={$transaction_type},
                                    payment_method=%s,
                                    status='{$status}'
                               WHERE id=%d",   rgar($lead,"form_id"), rgar($lead,"post_id"), rgar($lead,"is_starred"), rgar($lead,"is_read"), rgar($lead,"ip"), $source_url, $user_agent,
                                               rgar($lead,"currency"), rgar($lead,"payment_status"), rgar($lead,"transaction_id"), rgar($lead,"is_fulfilled"), rgar($lead, "payment_method"), rgar($lead,"id"));
        $wpdb->query($sql);

        self::set_current_lead($lead);
    }

    private static function truncate($str, $length){
        if(strlen($str) > $length){
            $str = substr($str, 0, $length);
        }
        return $str;
    }

    public static function delete_leads($leads){
        foreach($leads as $lead_id)
            self::delete_lead($lead_id);
    }

    public static function delete_forms($forms){
        foreach($forms as $form_id)
            self::delete_form($form_id);
    }

    public static function trash_forms($form_ids){
        foreach($form_ids as $form_id)
            self::trash_form($form_id);
    }

    public static function restore_forms($form_ids){
        foreach($form_ids as $form_id)
            self::restore_form($form_id);
    }

    public static function delete_leads_by_form($form_id, $status=""){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_delete_entries"))
            die(__("You don't have adequate permission to delete entries.", "gravityforms"));

        $lead_table = self::get_lead_table_name();
        $lead_notes_table = self::get_lead_notes_table_name();
        $lead_detail_table = self::get_lead_details_table_name();
        $lead_detail_long_table = self::get_lead_details_long_table_name();

        //deleting uploaded files
        self::delete_files_by_form($form_id, $status);

        $status_filter = empty($status) ? "" : $wpdb->prepare("AND status=%s", $status);

        //Delete from detail long
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_long_table
                                WHERE lead_detail_id IN(
                                    SELECT ld.id FROM $lead_detail_table ld
                                    INNER JOIN $lead_table l ON l.id = ld.lead_id
                                    WHERE l.form_id=%d AND ld.form_id=%d {$status_filter}
                                )", $form_id, $form_id);
        $wpdb->query($sql);

        //Delete from lead details
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_table
                                WHERE lead_id IN (
                                    SELECT id FROM $lead_table WHERE form_id=%d {$status_filter}
                                )", $form_id);
        $wpdb->query($sql);

        //Delete from lead notes
        $sql = $wpdb->prepare(" DELETE FROM $lead_notes_table
                                WHERE lead_id IN (
                                    SELECT id FROM $lead_table WHERE form_id=%d {$status_filter}
                                )", $form_id);
        $wpdb->query($sql);

        //Delete from lead
        $sql = $wpdb->prepare("DELETE FROM $lead_table WHERE form_id=%d {$status_filter}", $form_id);
        $wpdb->query($sql);
    }

    public static function delete_views($form_id){
        global $wpdb;

        $form_view_table = self::get_form_view_table_name();

        //Delete form view
        $sql = $wpdb->prepare("DELETE FROM $form_view_table WHERE form_id=%d", $form_id);
        $wpdb->query($sql);
    }

    public static function delete_form($form_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_delete_forms"))
            die(__("You don't have adequate permission to delete forms.", "gravityforms"));

        do_action("gform_before_delete_form", $form_id);

        $form_meta_table = self::get_meta_table_name();
        $form_table = self::get_form_table_name();

        //Deleting form Entries
        self::delete_leads_by_form($form_id);

        //Delete form meta
        $sql = $wpdb->prepare("DELETE FROM $form_meta_table WHERE form_id=%d", $form_id);
        $wpdb->query($sql);

        //Deleting form Views
        self::delete_views($form_id);

        //Delete form
        $sql = $wpdb->prepare("DELETE FROM $form_table WHERE id=%d", $form_id);
        $wpdb->query($sql);

        do_action("gform_after_delete_form", $form_id);
    }

    public static function trash_form($form_id){
        global $wpdb;
        $form_table_name = self::get_form_table_name();
        $sql = $wpdb->prepare("UPDATE $form_table_name SET is_trash=1 WHERE id=%d", $form_id) ;
        $result = $wpdb->query($sql);

        self::$_current_forms[$form_id] = null;
        $success = $result == false;
        return $success;
    }

    public static function restore_form($form_id){
        global $wpdb;
        $form_table_name = self::get_form_table_name();
        $sql = $wpdb->prepare("UPDATE $form_table_name SET is_trash=0 WHERE id=%d", $form_id) ;
        $result = $wpdb->query($sql);

        self::$_current_forms[$form_id] = null;
        $success = $result == false;
        return $success;
    }

    public static function duplicate_form($form_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_create_form"))
            die(__("You don't have adequate permission to create forms.", "gravityforms"));

        //finding unique title
        $form = self::get_form($form_id);
        $count = 2;
        $title = $form->title . " - Copy 1";
        while(!self::is_unique_title($title)){
            $title = $form->title . " - Copy $count";
            $count++;
        }

        //creating new form
        $new_id = self::insert_form($title);

        //copying form meta
        $meta = self::get_form_meta($form_id);
        $meta["title"] = $title;
        $meta["id"] = $new_id;

        $notifications = $meta["notifications"];
        $confirmations = $meta["confirmations"];
        unset($meta["notifications"]);
        unset($meta["confirmations"]);
        self::update_form_meta($new_id, $meta);

        //copying notification meta
        self::update_form_meta($new_id, $notifications, "notifications");

        //copying confirmation meta
        self::update_form_meta($new_id, $confirmations, "confirmations");
        return $new_id;
    }

    public static function is_unique_title($title){
        $forms = self::get_forms();
        foreach($forms as $form){
            if(strtolower($form->title) == strtolower($title))
                return false;
        }

        return true;
    }

    public static function ensure_tables_exist(){
        global $wpdb;
        $form_table_name = self::get_form_table_name();
        $form_count = $wpdb->get_var("SELECT count(0) FROM {$form_table_name}");
        if($wpdb->last_error){
            GFCommon::log_debug("Blog " . get_current_blog_id() . " - Form database table does not exist. Forcing database setup.");
            GFForms::setup_database();
        }
    }

    public static function insert_form($form_title){
        global $wpdb;
        $form_table_name =  $wpdb->prefix . "rg_form";

        //creating new form
        $wpdb->query($wpdb->prepare("INSERT INTO $form_table_name(title, date_created) VALUES(%s, utc_timestamp())", $form_title));

        //returning newly created form id
        return $wpdb->insert_id;

    }

    public static function update_form_meta($form_id, $form_meta, $meta_name="display_meta"){
        global $wpdb;
        $meta_table_name = self::get_meta_table_name();
        $form_meta = json_encode($form_meta);

        if(intval($wpdb->get_var($wpdb->prepare("SELECT count(0) FROM $meta_table_name WHERE form_id=%d", $form_id))) > 0)
            $result = $wpdb->query( $wpdb->prepare("UPDATE $meta_table_name SET $meta_name=%s WHERE form_id=%d", $form_meta, $form_id) );
        else
            $result = $wpdb->query( $wpdb->prepare("INSERT INTO $meta_table_name(form_id, $meta_name) VALUES(%d, %s)", $form_id, $form_meta ) );

        self::$_current_forms[$form_id] = null;

        return $result;
    }

    public static function delete_files($lead_id, $form=null){
        $lead = self::get_lead($lead_id);

        if($form == null)
            $form = self::get_form_meta($lead["form_id"]);

        $fields = GFCommon::get_fields_by_type($form, array("fileupload", "post_image"));
        if(is_array($fields)){
            foreach($fields as $field){
                if(rgar($field, "multipleFiles")){
                    $value_json = self::get_lead_field_value($lead, $field);
                    if(!empty($value_json)){
                        $files = json_decode($value_json, true);
                        if(false === empty($files) && is_array($files)){
                            foreach ($files as $file){
                                self::delete_physical_file($file);
                            }
                        }
                    }
                } else {
                    $value = self::get_lead_field_value($lead, $field);
                    self::delete_physical_file($value);
                }
            }
        }
    }

    public static function delete_files_by_form($form_id, $status=""){
        global $wpdb;
        $form = self::get_form_meta($form_id);
        $fields = GFCommon::get_fields_by_type($form, array("fileupload", "post_image"));
        if(empty($fields))
            return;

        $status_filter = empty($status) ? "" : $wpdb->prepare("AND status=%s", $status);
        $results = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}rg_lead WHERE form_id=%d {$status_filter}", $form_id), ARRAY_A);

        foreach($results as $result){
            self::delete_files($result["id"], $form);
        }
    }

    public static function delete_file($entry_id, $field_id, $file_index = 0){
        global $wpdb;

        if($entry_id == 0 || $field_id == 0)
            return;

        $entry = self::get_lead($entry_id);
        $form_id = $entry["form_id"];
        $form = self::get_form_meta($form_id);
        $field = self::get_field($form, $field_id);
        $multiple_files = rgar($field, "multipleFiles");
        if($multiple_files){
            $file_urls = json_decode($entry[$field_id], true);
            $file_url = $file_urls[$file_index];
            unset($file_urls[$file_index]);
            $file_urls = array_values($file_urls);
            $field_value = empty($file_urls) ? "" : json_encode($file_urls);
        } else {
            $file_url = $entry[$field_id];
            $field_value = "";
        }

        self::delete_physical_file($file_url);

        // update lead field value - simulate form submission

        $lead_detail_table = self::get_lead_details_table_name();
        $sql = $wpdb->prepare("SELECT id FROM $lead_detail_table WHERE lead_id=%d AND field_number BETWEEN %s AND %s", $entry_id, doubleval($field_id) - 0.001, doubleval($field_id) + 0.001);
        $entry_detail_id = $wpdb->get_var($sql);

        self::update_lead_field_value($form, $entry, $field, $entry_detail_id, $field_id, $field_value);

    }

    private static function delete_physical_file($file_url){
        $ary = explode("|:|", $file_url);
        $url = rgar($ary,0);
        if(empty($url))
            return;

        //Convert from url to physical path
        if (is_multisite() && get_site_option( 'ms_files_rewriting' )) {
            $file_path = preg_replace("|^(.*?)/files/gravity_forms/|", BLOGUPLOADDIR . "gravity_forms/", $url);
        } else {
            $file_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $url);
        }

        if(file_exists($file_path)){
            unlink($file_path);
        }
    }

    public static function delete_field($form_id, $field_id){
        global $wpdb;

        if($form_id == 0)
            return;

        do_action("gform_before_delete_field", $form_id, $field_id);

        $lead_table = self::get_lead_table_name();
        $lead_detail_table = self::get_lead_details_table_name();
        $lead_detail_long_table = self::get_lead_details_long_table_name();


        $form = self::get_form_meta($form_id);

        $field_type = "";

        //Deleting field from form meta
        $count = sizeof($form["fields"]);
        for($i = $count-1; $i >= 0; $i--){
            $field = $form["fields"][$i];

            //Deleting associated conditional logic rules
            if(!empty($field["conditionalLogic"])){
                $rule_count = sizeof($field["conditionalLogic"]["rules"]);
                for($j = $rule_count-1; $j >= 0; $j--){
                    if($field["conditionalLogic"]["rules"][$j]["fieldId"] == $field_id){
                        unset($form["fields"][$i]["conditionalLogic"]["rules"][$j]);
                    }
                }
                $form["fields"][$i]["conditionalLogic"]["rules"] = array_values($form["fields"][$i]["conditionalLogic"]["rules"]);

                //If there aren't any rules, remove the conditional logic
                if(sizeof($form["fields"][$i]["conditionalLogic"]["rules"]) == 0){
                    $form["fields"][$i]["conditionalLogic"] = false;
                }
            }

            //Deleting field from form meta
            if($field["id"] == $field_id){
                $field_type = $field["type"];
                unset($form["fields"][$i]);
            }

        }

        //removing post content and title template if the field being deleted is a post content field or post title field
        if($field_type == "post_content"){
            $form["postContentTemplateEnabled"] = false;
            $form["postContentTemplate"] = "";
        }
        else if($field_type == "post_title"){
            $form["postTitleTemplateEnabled"] = false;
            $form["postTitleTemplate"] = "";
        }

        //Deleting associated routing rules
        if(!empty($form["notification"]["routing"])){
            $routing_count = sizeof($form["notification"]["routing"]);
            for($j = $routing_count-1; $j >= 0; $j--){
                if(intval($form["notification"]["routing"][$j]["fieldId"]) == $field_id){
                    unset($form["notification"]["routing"][$j]);
                }
            }
            $form["notification"]["routing"] = array_values($form["notification"]["routing"]);

            //If there aren't any routing, remove it
            if(sizeof($form["notification"]["routing"]) == 0){
                $form["notification"]["routing"] = null;
            }
        }

        $form["fields"] = array_values($form["fields"]);
        self::update_form_meta($form_id, $form);

        //Delete from grid column meta
        $columns = self::get_grid_column_meta($form_id);
        $count = sizeof($columns);
        for($i = $count -1; $i >=0; $i--)
        {
            if(intval(rgar($columns,$i)) == intval($field_id)){
                unset($columns[$i]);
            }
        }
        self::update_grid_column_meta($form_id, $columns);

        //Delete from detail long
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_long_table
                                WHERE lead_detail_id IN(
                                    SELECT id FROM $lead_detail_table WHERE form_id=%d AND field_number >= %d AND field_number < %d
                                )", $form_id, $field_id, $field_id + 1);
        $wpdb->query($sql);

        //Delete from lead details
        $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE form_id=%d AND field_number >= %d AND field_number < %d", $form_id, $field_id, $field_id + 1);
        $wpdb->query($sql);

        //Delete leads with no details
        $sql = $wpdb->prepare(" DELETE FROM $lead_table
                                WHERE form_id=%d
                                AND id NOT IN(
                                    SELECT DISTINCT(lead_id) FROM $lead_detail_table WHERE form_id=%d
                                )", $form_id, $form_id);
        $wpdb->query($sql);

        do_action("gform_after_delete_field", $form_id, $field_id);
    }

    public static function delete_lead($lead_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_delete_entries"))
            die(__("You don't have adequate permission to delete entries.", "gravityforms"));

        do_action("gform_delete_lead", $lead_id);

        $lead_table = self::get_lead_table_name();
        $lead_notes_table = self::get_lead_notes_table_name();
        $lead_detail_table = self::get_lead_details_table_name();
        $lead_detail_long_table = self::get_lead_details_long_table_name();

        //deleting uploaded files
        self::delete_files($lead_id);

        //Delete from detail long
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_long_table
                                WHERE lead_detail_id IN(
                                    SELECT id FROM $lead_detail_table WHERE lead_id=%d
                                )", $lead_id);
        $wpdb->query($sql);

        //Delete from lead details
        $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE lead_id=%d", $lead_id);
        $wpdb->query($sql);

        //Delete from lead notes
        $sql = $wpdb->prepare("DELETE FROM $lead_notes_table WHERE lead_id=%d", $lead_id);
        $wpdb->query($sql);

        //Delete from lead meta
        gform_delete_meta($lead_id);

        //Delete from lead
        $sql = $wpdb->prepare("DELETE FROM $lead_table WHERE id=%d", $lead_id);
        $wpdb->query($sql);

    }

    public static function add_note($lead_id, $user_id, $user_name, $note){
        global $wpdb;

        $table_name = self::get_lead_notes_table_name();
        $sql = $wpdb->prepare("INSERT INTO $table_name(lead_id, user_id, user_name, value, date_created) values(%d, %d, %s, %s, utc_timestamp())", $lead_id, $user_id, $user_name, $note);

        $wpdb->query($sql);
    }

    public static function delete_note($note_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_edit_entry_notes"))
            die(__("You don't have adequate permission to delete notes.", "gravityforms"));

        $table_name = self::get_lead_notes_table_name();
        $sql = $wpdb->prepare("DELETE FROM $table_name WHERE id=%d", $note_id);
        $wpdb->query($sql);
    }

    public static function delete_notes($notes){
        if(!is_array($notes))
            return;

        foreach($notes as $note_id){
            self::delete_note($note_id);
        }
    }

    public static function get_ip(){

        $ip = '';
        $headers = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

        foreach( $headers as $header ) {
            $ip = rgar( $_SERVER, $header );
            if( $ip )
                break;
        }

        // HTTP_X_FORWARDED_FOR can return a comma separated list of IPs; using the first one
        $ips = explode( ',', $ip );

        return $ips[0];
    }

    public static function save_lead($form, &$lead){
        global $wpdb;

        GFCommon::log_debug("Saving entry.");

        if(IS_ADMIN && !GFCommon::current_user_can_any("gravityforms_edit_entries"))
            die(__("You don't have adequate permission to edit entries.", "gravityforms"));

        $lead_detail_table = self::get_lead_details_table_name();

        //Inserting lead if null
        if($lead == null){
            global $current_user;
            $user_id = $current_user && $current_user->ID ? $current_user->ID : 'NULL';

            $lead_table = RGFormsModel::get_lead_table_name();
            $user_agent = self::truncate($_SERVER["HTTP_USER_AGENT"], 250);
            $currency = GFCommon::get_currency();
            $source_url = self::truncate(self::get_current_page_url(), 200);

            $wpdb->query($wpdb->prepare("INSERT INTO $lead_table(form_id, ip, source_url, date_created, user_agent, currency, created_by) VALUES(%d, %s, %s, utc_timestamp(), %s, %s, {$user_id})", $form["id"], self::get_ip(), $source_url, $user_agent, $currency));


            //reading newly created lead id
            $lead_id = $wpdb->insert_id;
            $lead = array("id" => $lead_id);

            GFCommon::log_debug("Entry record created in the database. ID: {$lead_id}");
        }

        $current_fields = $wpdb->get_results($wpdb->prepare("SELECT id, field_number FROM $lead_detail_table WHERE lead_id=%d", $lead["id"]));
        $original_post_id = rgget("post_id", $lead);

        $total_fields = array();
        $calculation_fields = array();
        $recalculate_total = false;

        GFCommon::log_debug("Saving entry fields.");

        foreach($form["fields"] as $field){

            //Ignore fields that are marked as display only
            if(rgget("displayOnly", $field) && $field["type"] != "password"){
                continue;
            }

            //ignore pricing fields in the entry detail
            if(RG_CURRENT_VIEW == "entry" && GFCommon::is_pricing_field($field["type"])){
                continue;
            }


            //process total field after all fields have been saved
            if($field["type"] == "total"){
                $total_fields[] = $field;
                continue;
            }

            //only save fields that are not hidden (except on entry screen)
            if(RG_CURRENT_VIEW == "entry" || !RGFormsModel::is_field_hidden($form, $field, array()) ){

                // process calculation fields after all fields have been saved (moved after the is hidden check)
                if( GFCommon::has_field_calculation($field) ) {
                    $calculation_fields[] = $field;
                    continue;
                }

                GFCommon::log_debug("Saving field {$field["label"]}");

                if($field['type'] == 'post_category')
                    $field = GFCommon::add_categories_as_choices($field, '');

                if(isset($field["inputs"]) && is_array($field["inputs"])){

                    foreach($field["inputs"] as $input)
                        self::save_input($form, $field, $lead, $current_fields, $input["id"]);
                }
                else{
                    self::save_input($form, $field, $lead, $current_fields, $field["id"]);
                }
            }
        }

        if(!empty($calculation_fields)) {
            foreach($calculation_fields as $calculation_field) {

                GFCommon::log_debug("Saving calculated field {$calculation_field["label"]}");

                if(isset($calculation_field["inputs"]) && is_array($calculation_field["inputs"])){
                    foreach($calculation_field["inputs"] as $input) {
                        self::save_input($form, $calculation_field, $lead, $current_fields, $input["id"]);
                        self::refresh_lead_field_value($lead["id"], $input["id"]);
                    }
                }
                else{
                    self::save_input($form, $calculation_field, $lead, $current_fields, $calculation_field["id"]);
                    self::refresh_lead_field_value($lead["id"], $calculation_field["id"]);
                }

            }
            self::refresh_product_cache($form, $lead = RGFormsModel::get_lead($lead['id']));
        }

        //saving total field as the last field of the form.
        if(!empty($total_fields)) {
            foreach($total_fields as $total_field){
                GFCommon::log_debug("Saving total field.");
                self::save_input($form, $total_field, $lead, $current_fields, $total_field["id"]);
            }
        }
    }

    public static function create_lead($form) {
        global $current_user;

		$total_fields = array();
        $calculation_fields = array();

        $lead = array();
        $lead['id'] = null;
        $lead['post_id'] = null;
        $lead['date_created'] = null;
        $lead['form_id'] = $form['id'];
        $lead['ip'] = self::get_ip();
        $lead['source_url'] =  self::truncate(self::get_current_page_url(), 200);
        $lead['user_agent'] = strlen($_SERVER['HTTP_USER_AGENT']) > 250 ? substr($_SERVER['HTTP_USER_AGENT'], 0, 250) : $_SERVER['HTTP_USER_AGENT'];
        $lead['currency'] = GFCommon::get_currency();
        $lead['created_by'] = $current_user && $current_user->ID ? $current_user->ID : 'NULL';

        foreach($form['fields'] as $field) {

            // ignore fields that are marked as display only
            if(rgget('displayOnly', $field) && $field['type'] != 'password'){
                continue;
            }

            // process total field after all fields have been saved
            if($field['type'] == 'total'){
                $total_fields[] = $field;
                continue;
            }

            // process calculation fields after all fields have been saved
            if(GFCommon::has_field_calculation($field)) {
                $calculation_fields[] = $field;
                continue;
            }

            // only save fields that are not hidden
            if(!RGFormsModel::is_field_hidden($form, $field, array()) ){

                if($field['type'] == 'post_category')
                    $field = GFCommon::add_categories_as_choices($field, '');

                if(isset($field['inputs']) && is_array($field['inputs'])){
                    foreach($field['inputs'] as $input) {
                        $lead[(string)$input['id']] = self::get_prepared_input_value($form, $field, $lead, $input["id"]);
                    }
                }
                else {
                    $lead[$field['id']] = self::get_prepared_input_value($form, $field, $lead, $field["id"]);
                }
            }
        }

        if(!empty($calculation_fields)) {
            foreach($calculation_fields as $field) {

                // only save fields that are not hidden
                if(RGFormsModel::is_field_hidden($form, $field, array()) )
                    continue;

                if(isset($field["inputs"]) && is_array($field["inputs"])){
                    foreach($field["inputs"] as $input) {
                        $lead[(string)$input['id']] = self::get_prepared_input_value($form, $field, $lead, $input["id"]);
                    }
                }
                else{
                    $lead[$field['id']] = self::get_prepared_input_value($form, $field, $lead, $field["id"]);
                }

            }
            self::refresh_product_cache($form, $lead);
        }

        // saving total field as the last field of the form.
        if( ! empty( $total_fields ) ) {
            foreach( $total_fields as $total_field ) {
                $lead[$total_field['id']] = self::get_prepared_input_value( $form, $total_field, $lead, $total_field['id'] );
            }
        }

        return $lead;
    }

    public static function get_prepared_input_value($form, $field, $lead, $input_id) {

        $input_name = "input_" . str_replace('.', '_', $input_id);
        $value = rgpost($input_name);

        if(empty($value) && rgar($field, "adminOnly") && !IS_ADMIN){
            $value = self::get_default_value($field, $input_id);
        }

        switch(self::get_input_type($field)) {

        case "post_image":
            $file_info = self::get_temp_filename($form['id'], $input_name);
            $file_path = self::get_file_upload_path($form['id'], $file_info["uploaded_filename"]);
            $url = $file_path['url'];

            $image_title = isset($_POST["{$input_name}_1"]) ? strip_tags($_POST["{$input_name}_1"]) : "";
            $image_caption = isset($_POST["{$input_name}_4"]) ? strip_tags($_POST["{$input_name}_4"]) : "";
            $image_description = isset($_POST["{$input_name}_7"]) ? strip_tags($_POST["{$input_name}_7"]) : "";

            $value = !empty($url) ? $url . "|:|" . $image_title . "|:|" . $image_caption . "|:|" . $image_description : "";
            break;

        case "fileupload" :
            if(rgar($field, "multipleFiles")){
                $value = json_encode($value);
            } else{
                $file_info = self::get_temp_filename($form['id'], $input_name);
                $file_path = self::get_file_upload_path($form['id'], $file_info["uploaded_filename"]);
                $value = $file_path['url'];
            }

            break;

        default:

            // processing values so that they are in the correct format for each input type
            $value = self::prepare_value($form, $field, $value, $input_name, rgar($lead, 'id'), $lead);

        }

        return apply_filters("gform_save_field_value", $value, $lead, $field, $form);
    }

    public static function refresh_product_cache($form, $lead, $use_choice_text = false, $use_admin_label = false) {

        $cache_options = array(
            array(false, false),
            array(false, true),
            array(true, false),
            array(true, true)
            );

        foreach($form["fields"] as $field){
            if(GFCommon::has_field_calculation($field)){
                //deleting field value cache for calculated fields
                $cache_key = "GFFormsModel::get_lead_field_value_" . $lead["id"] . "_" . $field["id"];
                GFCache::delete($cache_key);
            }
        }

        foreach($cache_options as $cache_option) {
            list($use_choice_text, $use_admin_label) = $cache_option;
            if( gform_get_meta( rgar($lead,'id'), "gform_product_info_{$use_choice_text}_{$use_admin_label}") ) {
                gform_delete_meta(rgar($lead,'id'), "gform_product_info_{$use_choice_text}_{$use_admin_label}");
                GFCommon::get_product_fields($form, $lead, $use_choice_text, $use_admin_label);
            }
        }

    }

    /**
     * Check whether a field is hidden via conditional logic.
     *
     * @param array $form Form object.
     * @param array $field Field object.
     * @param array $field_values Default field values for this form. Used when form has not yet been submitted. Pass an array if no default field values are avilable/required.
     * @return array $lead Optional, default is null. If lead object is available, pass the lead.
     */
    public static function is_field_hidden($form, $field, $field_values, $lead=null){

        $cache_key = "GFFormsModel::is_field_hidden_" . $form["id"] . "_" . $field["id"];
        $display = GFCache::get($cache_key);
        if($display !== false)
            return $display;

        $section = self::get_section($form, $field["id"]);
        $section_display = self::get_field_display($form, $section, $field_values, $lead);

        //if section is hidden, hide field no matter what. if section is visible, see if field is supposed to be visible
        if($section_display == "hide"){
            $display = "hide";
        }
        else if(self::is_page_hidden($form, rgar($field,"pageNumber"), $field_values, $lead)){
            $display = "hide";
        }
        else{
            $display = self::get_field_display($form, $field, $field_values, $lead);
            return $display == "hide";
        }

        GFCache::set($cache_key, $display);

        return $display == "hide";
    }

    public static function is_page_hidden($form, $page_number, $field_values, $lead=null){
        $page = self::get_page_by_number($form, $page_number);

        if(!$page)
            return false;

        $display = self::get_field_display($form, $page, $field_values, $lead);
        return $display == "hide";
    }

    public static function get_page_by_number($form, $page_number){
        foreach($form["fields"] as $field){
            if($field["type"] == "page" && $field["pageNumber"] == $page_number)
                return $field;
        }
        return null;
    }

    public static function get_page_by_field($form, $field){
        return get_page_by_number($field["pageNumber"]);
    }

    //gets the section that the specified field belongs to, or null if none
    public static function get_section($form, $field_id){
        $current_section = null;
        foreach($form["fields"] as $field){
            if($field["type"] == "section")
                $current_section = $field;

            //stop section at a page break (sections don't go cross page)
            if($field["type"] == "page")
                $current_section = null;

            if($field["id"] == $field_id)
                return $current_section;
        }

        return null;
    }

    public static function is_value_match( $field_value, $target_value, $operation="is", $source_field = null, $rule = null ){

        $is_match = false;

        if($source_field && $source_field["type"] == "post_category")
            $field_value = GFCommon::prepare_post_category_value($field_value, $source_field, "conditional_logic");

        if (!empty($field_value) && !is_array($field_value) && $source_field["type"] == "multiselect")
			$field_value = explode(",", $field_value); // convert the comma-delimited string into an array

        $target_value = GFFormsModel::maybe_trim_input($target_value, rgar($source_field, "formId"),$source_field);

        if(is_array($field_value)){
            $field_value = array_values($field_value); //returning array values, ignoring keys if array is associative
            $match_count = 0;
            foreach($field_value as $val){
                $val = GFFormsModel::maybe_trim_input(GFCommon::get_selection_value($val), rgar($source_field,"formId"),$source_field);
                if(self::matches_operation($val, $target_value, $operation)){
                    $match_count++;
                }
            }
            // if operation is Is Not, none of the values in the array can match the target value.
            $is_match = $operation == "isnot" ? $match_count == count($field_value) : $match_count > 0;
        }
        else if(self::matches_operation(GFFormsModel::maybe_trim_input(GFCommon::get_selection_value($field_value), rgar($source_field,"formId"),$source_field), $target_value, $operation)){
            $is_match = true;
        }

        return apply_filters( 'gform_is_value_match', $is_match, $field_value, $target_value, $operation, $source_field, $rule );
    }

    private static function try_convert_float($text){
        global $wp_locale;
        $number_format = $wp_locale->number_format['decimal_point'] == "," ? "decimal_comma" : "decimal_dot";

        if(GFCommon::is_numeric($text, $number_format))
            return GFCommon::clean_number($text, $number_format);

        return $text;
    }

    public static function matches_operation($val1, $val2, $operation){

        $val1 = !rgblank($val1) ? strtolower($val1) : "";
        $val2 = !rgblank($val2) ? strtolower($val2) : "";

        switch($operation){
            case "is" :
                return $val1 == $val2;
            break;

            case "isnot" :
                return $val1 != $val2;
            break;

            case "greater_than":
            case ">" :
                $val1 = self::try_convert_float($val1);
                $val2 = self::try_convert_float($val2);

                return $val1 > $val2;
            break;

            case "less_than":
            case "<" :
                $val1 = self::try_convert_float($val1);
                $val2 = self::try_convert_float($val2);

                return $val1 < $val2;
            break;

            case "contains" :
                return !empty($val2) && strpos($val1, $val2) !== false;
            break;

            case "starts_with" :
                return !empty($val2) && strpos($val1, $val2) === 0;
            break;

            case "ends_with" :
                $start = strlen($val1) - strlen($val2);
                if($start < 0)
                    return false;

                $tail = substr($val1, $start);
                return $val2 == $tail;
            break;
        }


        return false;
    }

    private static function get_field_display($form, $field, $field_values, $lead=null){

        $logic = rgar($field, "conditionalLogic");

        //if this field does not have any conditional logic associated with it, it won't be hidden
        if(empty($logic))
            return "show";

        $match_count = 0;
        foreach($logic["rules"] as $rule){
            $source_field = RGFormsModel::get_field($form, $rule["fieldId"]);
            $field_value = empty($lead) ? self::get_field_value($source_field, $field_values) : self::get_lead_field_value($lead, $source_field);

            $is_value_match = self::is_value_match($field_value, $rule["value"], $rule["operator"], $source_field);

            if($is_value_match)
                $match_count++;
        }

        $do_action = ($logic["logicType"] == "all" && $match_count == sizeof($logic["rules"]) ) || ($logic["logicType"] == "any"  && $match_count > 0);
        $is_hidden = ($do_action && $logic["actionType"] == "hide") || (!$do_action && $logic["actionType"] == "show");

        return $is_hidden ? "hide" : "show";
    }

    public static function get_custom_choices(){
        $choices = get_option("gform_custom_choices");
        if(!$choices)
            $choices = array();

        return $choices;
    }

    public static function delete_custom_choice($name){
        $choices = self::get_custom_choices();
        if(array_key_exists($name, $choices))
            unset($choices[$name]);

        update_option("gform_custom_choices", $choices);
    }

    public static function save_custom_choice($previous_name, $new_name, $choices){
        $all_choices = self::get_custom_choices();

        if(array_key_exists($previous_name, $all_choices))
            unset($all_choices[$previous_name]);

        $all_choices[$new_name] = $choices;

        update_option("gform_custom_choices", $all_choices);
    }

    public static function get_field_value(&$field, $field_values = array(), $get_from_post=true){

        if($field['type'] == 'post_category')
            $field = GFCommon::add_categories_as_choices($field, '');

        $value = array();
        switch(RGFormsModel::get_input_type($field)){
            case "post_image" :
                $value[$field["id"] . ".1"] = self::get_input_value($field, "input_" . $field["id"] . "_1", $get_from_post);
                $value[$field["id"] . ".4"] = self::get_input_value($field, "input_" . $field["id"] . "_4", $get_from_post);
                $value[$field["id"] . ".7"] = self::get_input_value($field, "input_" . $field["id"] . "_7", $get_from_post);
            break;
            case "checkbox" :
                $parameter_values = self::get_parameter_value($field["inputName"], $field_values, $field);
                if(!empty($parameter_values) && !is_array($parameter_values)){
                    $parameter_values = explode(",", $parameter_values);
                }

                if(!is_array($field["inputs"]))
                    return "";

                $choice_index = 0;
                foreach($field["inputs"] as $input){
                    if(!empty($_POST["is_submit_" . $field["formId"]]) && $get_from_post){
                        $value[strval($input["id"])] = rgpost("input_" . str_replace('.', '_', strval($input["id"])));
                    }
                    else{
                        if(is_array($parameter_values)){
                            foreach($parameter_values as $item){
                                $item = trim($item);
                                if(self::choice_value_match($field, $field["choices"][$choice_index], $item))
                                {
                                    $value[$input["id"] . ""] = $item;
                                    break;
                                }
                            }
                        }
                    }
                    $choice_index++;
                }

            break;

            case "list" :
                $value = self::get_input_value($field, "input_" . $field["id"], rgar($field, "inputName"), $field_values, $get_from_post);
                $value = self::create_list_array($field, $value);
            break;

            case "number" :
                $value = self::get_input_value($field, "input_" . $field["id"], rgar($field, "inputName"), $field_values, $get_from_post);
                $value = trim($value);
                if($field["numberFormat"] == "currency"){
                    require_once(GFCommon::get_base_path() . '/currency.php');
                    $currency = new RGCurrency(GFCommon::get_currency());
                    $value = $currency->to_number( $value );
                }
            break;

            case "radio" :
            	$value = self::get_input_value($field, "input_" . $field["id"], rgar($field, "inputName"), $field_values, $get_from_post);
            	if ($value == "gf_other_choice"){
					//get value from text box
					$value = self::get_input_value($field, "input_" . $field["id"] . "_other", rgar($field, "inputName"), $field_values, $get_from_post);
            	}
            break;

            default:

                if(isset($field["inputs"]) && is_array($field["inputs"])){
                    foreach($field["inputs"] as $input){
                        $value[strval($input["id"])] = self::get_input_value($field, "input_" . str_replace('.', '_', strval($input["id"])), RGForms::get("name", $input), $field_values, $get_from_post);
                    }
                }
                else{
                    $value = self::get_input_value($field, "input_" . $field["id"], rgar($field, "inputName"), $field_values, $get_from_post);
                }
            break;
        }

        return $value;
    }

    public static function maybe_trim_input($value, $form_id, $field){
        $trim_value = apply_filters("gform_trim_input_value", true, $form_id, $field);

        if($trim_value)
            $value = is_array($value) ? array_map('trim', $value) : trim($value);

        return $value;
    }

    private static function get_input_value($field, $standard_name, $custom_name = "", $field_values=array(), $get_from_post=true){
        $form_id = rgar($field,"formId");
        if(!empty($_POST["is_submit_" . $form_id]) && $get_from_post){
            $value = rgpost($standard_name);
            $value = self::maybe_trim_input($value, $form_id, $field);
            return $value;
        }
        else if(rgar($field, "allowsPrepopulate")){
            return self::get_parameter_value($custom_name, $field_values, $field);
        }
    }

    public static function get_parameter_value($name, $field_values, $field){
        $value = stripslashes(rgget($name));
        if(empty($value))
            $value = rgget($name, $field_values);

        //converting list format
        if(RGFormsModel::get_input_type($field) == "list"){

            //transforms this: col1|col2,col1b|col2b into this: col1,col2,col1b,col2b
            $column_count = count(rgar($field,"choices"));

            $rows = explode(",", $value);
            $ary_rows = array();
            if(!empty($rows)){
                foreach($rows as $row)
                    $ary_rows = array_merge($ary_rows, rgexplode("|", $row, $column_count));

                $value = $ary_rows;
            }
        }

        return apply_filters("gform_field_value_$name", apply_filters("gform_field_value", $value, $field), $field);
    }

    public static function get_default_value($field, $input_id){
        if(!is_array(rgar($field,"choices"))){
            if(is_array(rgar($field, "inputs"))){
                $input = RGFormsModel::get_input($field, $input_id);
                return rgar($input, "defaultValue");
            }
            else{
                return IS_ADMIN ? $field["defaultValue"] : GFCommon::replace_variables_prepopulate($field["defaultValue"]);
            }
        }
        else if($field["type"] == "checkbox"){
            for($i=0, $count=sizeof($field["inputs"]); $i<$count; $i++){
                $input = $field["inputs"][$i];
                $choice = $field["choices"][$i];
                if($input["id"] == $input_id && $choice["isSelected"]){
                    return $choice["value"];
                }
            }
            return "";
        }
        else{
            foreach($field["choices"] as $choice){
                if(rgar($choice,"isSelected") || $field["type"] == "post_category")
                    return $choice["value"];
            }
            return "";
        }

    }

    public static function get_input_type($field){
        return empty($field["inputType"]) ? rgar($field,"type") : $field["inputType"];
    }

    private static function get_post_field_value($field, $lead){

        if(isset($field["inputs"]) && is_array($field["inputs"])){
            $value = array();
            foreach($field["inputs"] as $input){
                $val = isset($lead[strval($input["id"])]) ? $lead[strval($input["id"])] : "";
                if(!empty($val)) {

                    // replace commas in individual values to prevent individual value from being split into multiple values (checkboxes, multiselects)
                    if( in_array(RGFormsModel::get_input_type($field), array('checkbox', 'multiselect')) )
                        $val = str_replace(',', '&#44;', $val);

                    $value[] = $val;
                }
            }
            $value = implode(",", $value);
        }
        else{
            $value = isset($lead[$field["id"]]) ? $lead[$field["id"]] : "";
        }
        return $value;
    }

    private static function get_post_fields($form, $lead) {

        $post_data = array();
        $post_data["post_custom_fields"] = array();
        $post_data["tags_input"] = array();
        $categories = array();
        $images = array();

        foreach($form["fields"] as $field){

            if($field['type'] == 'post_category')
                $field = GFCommon::add_categories_as_choices($field, '');

            $value = self::get_post_field_value($field, $lead);

            switch($field["type"]){
                case "post_title" :
                case "post_excerpt" :
                case "post_content" :
                    $post_data[$field["type"]] = $value;
                break;

                case "post_tags" :
                    $tags = explode(",", $value);
                    if(is_array($tags) && sizeof($tags) > 0)
                        $post_data["tags_input"] = array_merge($post_data["tags_input"], $tags) ;
                break;

                case "post_custom_field" :

                    $type = self::get_input_type($field);
                    if("fileupload" === $type && rgar($field, "multipleFiles"))
                        $value = json_decode($value, true);

                    $meta_name = $field["postCustomFieldName"];

                    if(!isset($post_data["post_custom_fields"][$meta_name])){
                        $post_data["post_custom_fields"][$meta_name] = $value;
                    }
                    else if(!is_array($post_data["post_custom_fields"][$meta_name])){
                        $post_data["post_custom_fields"][$meta_name] = array($post_data["post_custom_fields"][$meta_name], $value);
                    }
                    else{
                        $post_data["post_custom_fields"][$meta_name][] = $value;
                    }

                break;

                case "post_category" :
                    foreach(explode(',', $value) as $cat_string) {
                        list($cat_name, $cat_id) = rgexplode(":", $cat_string, 2);
                        array_push($categories, $cat_id);
                    }
                break;

                case "post_image" :
                    $ary = !empty($value) ? explode("|:|", $value) : array();
                    $url = count($ary) > 0 ? $ary[0] : "";
                    $title = count($ary) > 1 ? $ary[1] : "";
                    $caption = count($ary) > 2 ? $ary[2] : "";
                    $description = count($ary) > 3 ? $ary[3] : "";

                    array_push($images, array("field_id" => $field["id"], "url" => $url, "title" => $title, "description" => $description, "caption" => $caption));
                break;
            }
        }

        $post_data["post_status"] = rgar($form, "postStatus");
        $post_data["post_category"] = !empty($categories) ? $categories : array(rgar($form, 'postCategory'));
        $post_data["images"] = $images;

        //setting current user as author depending on settings
        $post_data["post_author"] = $form["useCurrentUserAsAuthor"] && !empty($lead["created_by"]) ? $lead["created_by"] : $form["postAuthor"];

        return $post_data;
    }

    public static function get_custom_field_names(){
        global $wpdb;
        $keys = $wpdb->get_col( "
        SELECT meta_key
        FROM $wpdb->postmeta
        WHERE meta_key NOT LIKE '\_%'
        GROUP BY meta_key
        ORDER BY meta_id DESC");

        if ( $keys )
            natcasesort($keys);

        return $keys;
    }

    public static function get_input_masks(){

        $masks = array(
            'US Phone' => '(999) 999-9999',
            'US Phone + Ext' => '(999) 999-9999? x99999',
            'Date' => '99/99/9999',
            'Tax ID' => '99-9999999',
            'SSN' => '999-99-9999',
            'Zip Code' => '99999',
            'Full Zip Code' => '99999?-9999'
            );

        return apply_filters('gform_input_masks', $masks);
    }

    private static function get_default_post_title(){
        global $wpdb;
        $title = "Untitled";
        $count = 1;

        $titles = $wpdb->get_col("SELECT post_title FROM $wpdb->posts WHERE post_title like '%Untitled%'");
        $titles = array_values($titles);
        while(in_array($title, $titles)){
            $title = "Untitled_$count";
            $count++;
        }
        return $title;
    }

    public static function prepare_date($date_format, $value){
        $format = empty($date_format) ? "mdy" : $date_format;
        $date_info = GFCommon::parse_date($value, $format);
        if(!empty($date_info) && !GFCommon::is_empty_array($date_info))
            $value = sprintf("%s-%02d-%02d", $date_info["year"], $date_info["month"], $date_info["day"]);
        else
            $value = "";

        return $value;
    }

    /**
    * Prepare the value before saving it to the lead.
    *
    * @param mixed $form
    * @param mixed $field
    * @param mixed $value
    * @param mixed $input_name
    * @param mixed $lead_id the current lead ID, used for fields that are processed after other fields have been saved (ie Total, Calculations)
    * @param mixed $lead passed by the RGFormsModel::create_lead() method, lead ID is not available for leads created by this function
    */
    public static function prepare_value($form, $field, $value, $input_name, $lead_id, $lead = array()){
        $form_id = $form["id"];

        $input_type = self::get_input_type($field);
        switch($input_type)
        {
            case "total" :
                $lead = empty($lead) ? RGFormsModel::get_lead($lead_id) : $lead;
                $value = GFCommon::get_order_total($form, $lead);
            break;

            case "calculation" :
                // ignore submitted value and recalculate price in backend
                list(,,$input_id) = rgexplode("_", $input_name, 3);
                if($input_id == 2) {
                    require_once(GFCommon::get_base_path() . '/currency.php');
                    $currency = new RGCurrency(GFCommon::get_currency());
                    $lead = empty($lead) ? RGFormsModel::get_lead($lead_id) : $lead;
                    $value = $currency->to_money(GFCommon::calculate($field, $form, $lead));
                }
            break;

            case "phone" :
                if($field["phoneFormat"] == "standard" && preg_match('/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/', $value, $matches))
                    $value = sprintf("(%s)%s-%s", $matches[1], $matches[2], $matches[3]);
            break;

            case "time":

                if(!is_array($value) && !empty($value)){
                    preg_match('/^(\d*):(\d*) ?(.*)$/', $value, $matches);
                    $value = array();
                    $value[0] = $matches[1];
                    $value[1] = $matches[2];
                    $value[2] = rgar($matches,3);
                }

                $hour = empty($value[0]) ? "0" : strip_tags($value[0]);
                $minute = empty($value[1]) ? "0" : strip_tags($value[1]);
                $ampm = strip_tags(rgar($value,2));
                if(!empty($ampm))
                    $ampm = " $ampm";

                if(!(empty($hour) && empty($minute)))
                    $value = sprintf("%02d:%02d%s", $hour, $minute, $ampm);
                else
                    $value = "";

            break;

            case "date" :
                $value = self::prepare_date($field["dateFormat"], $value);

            break;

            case "post_image":
                $url = self::get_fileupload_value($form_id, $input_name);
                $image_title = isset($_POST["{$input_name}_1"]) ? strip_tags($_POST["{$input_name}_1"]) : "";
                $image_caption = isset($_POST["{$input_name}_4"]) ? strip_tags($_POST["{$input_name}_4"]) : "";
                $image_description = isset($_POST["{$input_name}_7"]) ? strip_tags($_POST["{$input_name}_7"]) : "";

                $value = !empty($url) ? $url . "|:|" . $image_title . "|:|" . $image_caption . "|:|" . $image_description : "";
            break;

            case "fileupload" :
                if(rgar($field, "multipleFiles")){
                    if(isset(GFFormsModel::$uploaded_files[$form_id][$input_name])){
                        $uploaded_temp_files = GFFormsModel::$uploaded_files[$form_id][$input_name];
                        $uploaded_files = array();
                        foreach($uploaded_temp_files as $i => $file_info){
                            $temp_filepath = self::get_upload_path($form_id) . "/tmp/" . $file_info["temp_filename"];
                            if($file_info && file_exists($temp_filepath)){
                                $uploaded_files[$i] = self::move_temp_file($form_id, $file_info);
                            }
                        }

                        if(!empty($value)){ // merge with existing files (admin edit entry)
                            $value = json_decode($value, true);
                            $value = array_merge($value, $uploaded_files);
                            $value = json_encode($value);
                        } else {
                            $value = json_encode($uploaded_files);
                        }

                    } else {
                        $value = "";
                    }

                } else
                    $value = self::get_fileupload_value($form_id, $input_name);
            break;

            case "number" :

                $value = GFCommon::maybe_add_leading_zero($value);

                $is_hidden = RGFormsModel::is_field_hidden($form, $field, array());
                $lead = empty($lead) ? RGFormsModel::get_lead($lead_id) : $lead;
                $value = GFCommon::has_field_calculation($field) ? GFCommon::round_number(GFCommon::calculate($field, $form, $lead), rgar($field, "calculationRounding")) : GFCommon::clean_number($value, rgar($field, "numberFormat"));
                //return the value as a string when it is zero and a calc so that the "==" comparison done when checking if the field has changed isn't treated as false
                if (GFCommon::has_field_calculation($field) && $value == 0){
					$value = "0";
                }
            break;

            case "website" :
                if($value == "http://")
                    $value = "";
            break;

            case "list" :

                if(rgar($field, "adminOnly") && rgar($field, "allowsPrepopulate"))
                    $value = json_decode($value);

                if(GFCommon::is_empty_array($value))
                    $value = "";
                else{
                    $value = self::create_list_array($field, $value);
                    $value = serialize($value);
                }
            break;

            case "radio" :
                if(rgar($field, 'enableOtherChoice') && $value == 'gf_other_choice')
                    $value = rgpost("input_{$field['id']}_other");
                break;

            case "multiselect" :
                $value = empty($value) ? "" : is_array($value) ? implode(",", $value) : $value;
                break;

            case "creditcard" :
                //saving last 4 digits of credit card
                list($input_token, $field_id_token, $input_id) = rgexplode("_", $input_name, 3);
                if($input_id == "1"){
                    $value = str_replace(" ", "", $value);
                    $card_number_length = strlen($value);
                    $value = substr($value, -4, 4);
                    $value = str_pad($value, $card_number_length, "X", STR_PAD_LEFT);
                }
                else if( $input_id == '4' ) {

                    $value = rgpost( "input_{$field_id_token}_4" );

                    if( ! $value ) {
                        $card_number = rgpost( "input_{$field_id_token}_1" );
                        $card_type = GFCommon::get_card_type( $card_number );
                        $value = $card_type ? $card_type['name'] : '';
                    }

                }
                else{
                    $value = "";
                }

            break;

            default:

                //allow HTML for certain field types
                $allow_html = in_array($field["type"], array("post_custom_field", "post_title", "post_content", "post_excerpt", "post_tags")) || in_array($input_type, array("checkbox", "radio")) ? true : false;
                $allowable_tags = apply_filters("gform_allowable_tags_{$form_id}", apply_filters("gform_allowable_tags", $allow_html, $field, $form_id), $field, $form_id);

                if($allowable_tags !== true)
                    $value = strip_tags($value, $allowable_tags);

            break;
        }

        // special format for Post Category fields
        if($field['type'] == 'post_category') {

            $full_values = array();

            if(!is_array($value))
                $value = explode(',', $value);

            foreach($value as $cat_id) {
                $cat = get_term($cat_id, 'category');
                $full_values[] = !is_wp_error($cat) && is_object($cat) ? $cat->name . ":" . $cat_id : "";
            }

            $value = implode(',', $full_values);
        }

        //do not save price fields with blank price
        if(rgar($field, "enablePrice")){
            $ary = explode("|", $value);
            $label = count($ary) > 0 ? $ary[0] : "";
            $price = count($ary) > 1 ? $ary[1] : "";

            $is_empty = (strlen(trim($price)) <= 0);
            if($is_empty)
                $value = "";
        }

        return $value;
    }

    private static function create_list_array($field, $value){
        if(!rgar($field,"enableColumns")){
            return $value;
        }
        else{
            $col_count = count(rgar($field, "choices"));
            $rows = array();

            $row_count = count($value)/$col_count;

            $col_index = 0;
            for($i=0; $i<$row_count; $i++){
                $row = array();
                foreach($field["choices"] as $column){
                    $row[$column["text"]] = rgar($value,$col_index);
                    $col_index++;
                }
                $rows[] = $row;
            }
            return $rows;
        }
    }

    private static function get_fileupload_value($form_id, $input_name){
        global $_gf_uploaded_files;

        GFCommon::log_debug("Starting GFFormsModel::get_fileupload_value()");

        if(empty($_gf_uploaded_files)){
            GFCommon::log_debug("No files uploaded. Exiting GFFormsModel::get_fileupload_value()");
            $_gf_uploaded_files = array();
        }


        if(!isset($_gf_uploaded_files[$input_name])){

            //check if file has already been uploaded by previous step
            $file_info = self::get_temp_filename($form_id, $input_name);
            $temp_filepath = self::get_upload_path($form_id) . "/tmp/" . $file_info["temp_filename"];
            GFCommon::log_debug("get_fileupload_value() - temp file path: " . $temp_filepath);
            if($file_info && file_exists($temp_filepath)){
                GFCommon::log_debug("get_fileupload_value() - moving temp file: " . $temp_filepath);
                $_gf_uploaded_files[$input_name] = self::move_temp_file($form_id, $file_info);
            } else if(!empty($_FILES[$input_name]["name"])) {
                GFCommon::log_debug("get_fileupload_value() - uploading file: " . $_FILES[$input_name]["name"]);
                $_gf_uploaded_files[$input_name] = self::upload_file($form_id, $_FILES[$input_name]);
            }
        }

        return rgget($input_name, $_gf_uploaded_files);
    }

    public static function get_form_unique_id($form_id) {
        $unique_id = "";
        if (rgpost("gform_submit") == $form_id) {
            $posted_uid = rgpost("gform_unique_id");
            if (false === empty($posted_uid)) {
                $unique_id = $posted_uid;
                self::$unique_ids[$form_id] = $unique_id;
            } elseif (isset(self::$unique_ids[$form_id])) {
                $unique_id = self::$unique_ids[$form_id];
            } else {
                $unique_id =  uniqid();
                self::$unique_ids[$form_id] = $unique_id;
            }
        }

        return $unique_id;
    }

    public static function get_temp_filename($form_id, $input_name){

        $uploaded_filename = !empty($_FILES[$input_name]["name"]) && $_FILES[$input_name]["error"] === 0 ? $_FILES[$input_name]["name"] : "";

        if(empty($uploaded_filename) && isset(self::$uploaded_files[$form_id]))
            $uploaded_filename = rgget($input_name, self::$uploaded_files[$form_id]);

        if(empty($uploaded_filename))
            return false;

        $form_unique_id = self::get_form_unique_id($form_id);
        $pathinfo = pathinfo($uploaded_filename);
        return array("uploaded_filename" => $uploaded_filename, "temp_filename" => "{$form_unique_id}_{$input_name}.{$pathinfo["extension"]}");

    }

    public static function get_choice_text($field, $value, $input_id=0){
        if(!is_array(rgar($field, "choices")))
            return $value;

        foreach($field["choices"] as $choice){
            if(is_array($value) && self::choice_value_match($field, $choice, $value[$input_id])){
                return $choice["text"];
            }
            else if(!is_array($value) && self::choice_value_match($field, $choice, $value)){
                return $choice["text"];
            }
        }
        return is_array($value) ? "" : $value;
    }


    public static function choice_value_match($field, $choice, $value){
        $choice_value = GFFormsModel::maybe_trim_input($choice["value"], rgar($field, "formId"), $field);
        $value = GFFormsModel::maybe_trim_input($value, rgar($field, "formId"), $field);
        if($choice_value == $value){
           return true;
        }
        else if(rgget("enablePrice", $field)){
            $ary = explode("|", $value);
            $val = count($ary) > 0 ? $ary[0] : "";
            $price = count($ary) > 1 ? $ary[1] : "";

            if($val == $choice["value"])
                return true;
        }
        // add support for prepopulating multiselects @alex
        else if(RGFormsModel::get_input_type($field) == 'multiselect') {
            $values = explode(',', $value);
            if(in_array($choice_value, $values))
                return true;
        }
        return false;
    }

    public static function choices_value_match($field, $choices, $value) {
        foreach($choices as $choice){
            if(self::choice_value_match($field, $choice, $value))
                return true;
        }

        return false;
    }

    public static function create_post($form, &$lead){

        GFCommon::log_debug("Starting GFFormsModel::create_post()");

        $has_post_field = false;
        foreach($form["fields"] as $field){
            $is_hidden = self::is_field_hidden($form, $field, array(), $lead);
            if(!$is_hidden && in_array($field["type"], array("post_category","post_title","post_content","post_excerpt","post_tags","post_custom_field","post_image"))){
                $has_post_field = true;
                break;
            }
        }

        //if this form does not have any post fields, don't create a post
        if(!$has_post_field){
            GFCommon::log_debug("The form doesn't have any post fields - stopping.");
            return $lead;
        }


        //processing post fields
        GFCommon::log_debug("Getting post fields.");
        $post_data = self::get_post_fields($form, $lead);

        //allowing users to change post fields before post gets created
        $post_data = apply_filters("gform_post_data_{$form["id"]}", apply_filters("gform_post_data", $post_data , $form, $lead), $form, $lead);

        //adding default title if none of the required post fields are in the form (will make sure wp_insert_post() inserts the post)
        if(empty($post_data["post_title"]) && empty($post_data["post_content"]) && empty($post_data["post_excerpt"])){
            $post_data["post_title"] = self::get_default_post_title();
        }

        // remove original post status and save it for later
        $post_status = $post_data['post_status'];

        // replace original post status with 'draft' so other plugins know this post is not fully populated yet
        $post_data['post_status'] = 'draft';

        // inserting post
        GFCommon::log_debug("Inserting post");
        $post_id = wp_insert_post( $post_data );
        GFCommon::log_debug("Done inserting post");

        //adding form id and entry id hidden custom fields
        add_post_meta($post_id, "_gform-form-id", $form["id"]);
        add_post_meta($post_id, "_gform-entry-id", $lead["id"]);

        //creating post images
        GFCommon::log_debug("Creating post images.");
        $post_images = array();
        foreach($post_data["images"] as $image){
            $image_meta= array( "post_excerpt" => $image["caption"],
                                "post_content" => $image["description"]);

            //adding title only if it is not empty. It will default to the file name if it is not in the array
            if(!empty($image["title"]))
                $image_meta["post_title"] = $image["title"];

            if(!empty($image["url"])){
                GFCommon::log_debug("Adding image: " . $image["url"]);
                $media_id = self::media_handle_upload($image["url"], $post_id, $image_meta);

                if($media_id){

                    //save media id for post body/title template variable replacement (below)
                    $post_images[$image["field_id"]] = $media_id;
                    $lead[$image["field_id"]] .= "|:|$media_id";

                    // set featured image
                    $field = RGFormsModel::get_field($form, $image["field_id"]);
                    if(rgar($field, 'postFeaturedImage'))
                        set_post_thumbnail($post_id, $media_id);
                }
            }
        }

        //adding custom fields
        GFCommon::log_debug("Adding custom fields.");
        foreach($post_data["post_custom_fields"] as $meta_name => $meta_value) {
            if(!is_array($meta_value))
                $meta_value = array($meta_value);

            $meta_index = 0;
            foreach($meta_value as $value){
                GFCommon::log_debug("Getting custom field: " . $meta_name);
                $custom_field = self::get_custom_field($form, $meta_name, $meta_index);

                //replacing template variables if template is enabled
                if($custom_field && rgget("customFieldTemplateEnabled", $custom_field)){
                    //replacing post image variables
                    GFCommon::log_debug("Replacing post image variables.");
                    $value = GFCommon::replace_variables_post_image($custom_field["customFieldTemplate"], $post_images, $lead);

                    //replacing all other variables
                    $value = GFCommon::replace_variables($value, $form, $lead, false, false, false);

                    // replace conditional shortcodes
                    $value = do_shortcode($value);
                }
                switch(RGFormsModel::get_input_type($custom_field)){
                    case "list" :
                        $value = maybe_unserialize($value);
                        if(is_array($value)){
                            foreach($value as $item){
                                if(is_array($item))
                                    $item = implode("|", $item);

                                if(!rgblank($item))
                                    add_post_meta($post_id, $meta_name, $item);
                            }
                        }
                    break;

                    case "multiselect" :
                    case "checkbox" :
                        $value = explode(",", $value);
                        if(is_array($value)){
                            foreach($value as $item){
                                if(!rgblank($item)) {
                                    // add post meta and replace HTML symbol in $item with real comma
                                    add_post_meta($post_id, $meta_name, str_replace('&#44;', ',', $item));
                                }
                            }
                        }
                    break;

                    case "date" :
                        $value = GFCommon::date_display($value, rgar($custom_field, "dateFormat"));
                        if(!rgblank($value))
                            add_post_meta($post_id, $meta_name, $value);
                    break;

                    default :
                        if(!rgblank($value))
                            add_post_meta($post_id, $meta_name, $value);
                    break;
                }

                $meta_index++;
            }
        }

        $has_content_field = sizeof(GFCommon::get_fields_by_type($form, array("post_content"))) > 0;
        $has_title_field = sizeof(GFCommon::get_fields_by_type($form, array("post_title"))) > 0;
        $post = false;

        //if a post field was configured with a content or title template, process template
        if( (rgar($form, "postContentTemplateEnabled") && $has_content_field) || (rgar($form, "postTitleTemplateEnabled") && $has_title_field) ){
            GFCommon::log_debug("Processing template.");
            $post = get_post($post_id);

            if($form["postContentTemplateEnabled"] && $has_content_field){

                //replacing post image variables
                $post_content = GFCommon::replace_variables_post_image($form["postContentTemplate"], $post_images, $lead);

                //replacing all other variables
                $post_content = GFCommon::replace_variables($post_content, $form, $lead, false, false, false);

                //updating post content
                $post->post_content = $post_content;
            }

            if($form["postTitleTemplateEnabled"] && $has_title_field){

                //replacing post image variables
                $post_title = GFCommon::replace_variables_post_image($form["postTitleTemplate"], $post_images, $lead);

                //replacing all other variables
                $post_title = GFCommon::replace_variables($post_title, $form, $lead, false, false, false);

                // replace conditional shortcodes
                $post_title = do_shortcode($post_title);

                //updating post
                $post->post_title = $post_title;

                $post->post_name = $post_title;
            }

        }

        // update post status back to original status (if not draft)
        if( $post_status != 'draft' ) {
            $post = is_object( $post ) ? $post : get_post( $post_id );
            $post->post_status = $post_status;
        }

        // if post has been modified since creation, save updates
        if( is_object( $post ) ){
            GFCommon::log_debug("Updating post.");
            wp_update_post($post);
        }


        //adding post format
        if(current_theme_supports('post-formats') && rgar($form, 'postFormat')) {

            $formats = get_theme_support('post-formats');
            $post_format = rgar($form, 'postFormat');

            if(is_array($formats)) {
                $formats = $formats[0];
                if(in_array( $post_format, $formats)) {
                    set_post_format($post_id, $post_format);
                } else if('0' == $post_format) {
                    set_post_format($post_id, false);
                }
            }

        }

        //update post_id field if a post was created
        $lead["post_id"] = $post_id;
        GFCommon::log_debug("Updating entry.");
        self::update_lead($lead);

        do_action( 'gform_after_create_post', $post_id );

        return $post_id;
    }

    private static function get_custom_field($form, $meta_name, $meta_index){
        $custom_fields = GFCommon::get_fields_by_type($form, array("post_custom_field"));

        $index = 0;
        foreach($custom_fields as $field){
            if($field["postCustomFieldName"] == $meta_name){
                if($meta_index == $index){
                    return $field;
                }
                $index++;
            }
        }
        return false;
    }

    private static function copy_post_image($url, $post_id){
        $time = current_time('mysql');

        if ( $post = get_post($post_id) ) {
            if ( substr( $post->post_date, 0, 4 ) > 0 )
                $time = $post->post_date;
        }

        //making sure there is a valid upload folder
        if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
            return false;

        $name = basename($url);

        $filename = wp_unique_filename($uploads['path'], $name);

        // Move the file to the uploads dir
        $new_file = $uploads['path'] . "/$filename";

        $uploaddir = wp_upload_dir();
        $path = str_replace($uploaddir["baseurl"], $uploaddir["basedir"], $url);

        if(!copy($path, $new_file))
            return false;

        // Set correct file permissions
        $stat = stat( dirname( $new_file ));
        $perms = $stat['mode'] & 0000666;
        @ chmod( $new_file, $perms );

        // Compute the URL
        $url = $uploads['url'] . "/$filename";

        if ( is_multisite() )
            delete_transient( 'dirsize_cache' );

        $type = wp_check_filetype($new_file);
        return array("file" => $new_file, "url" => $url, "type" => $type["type"]);

    }

    private static function media_handle_upload($url, $post_id, $post_data = array()) {

        //WordPress Administration API required for the media_handle_upload() function
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $name = basename($url);

        $file = self::copy_post_image($url, $post_id);

        if(!$file)
            return false;

        $name_parts = pathinfo($name);
        $name = trim( substr( $name, 0, -(1 + strlen($name_parts['extension'])) ) );

        $url = $file['url'];
        $type = $file['type'];
        $file = $file['file'];
        $title = $name;
        $content = '';

        // use image exif/iptc data for title and caption defaults if possible
        if ( $image_meta = @wp_read_image_metadata($file) ) {
            if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
                $title = $image_meta['title'];
            if ( trim( $image_meta['caption'] ) )
                $content = $image_meta['caption'];
        }

        // Construct the attachment array
        $attachment = array_merge( array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $post_id,
            'post_title' => $title,
            'post_content' => $content,
        ), $post_data );

        // Save the data
        $id = wp_insert_attachment($attachment, $file, $post_id);
        if ( !is_wp_error($id) ) {
            wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
        }

        return $id;
    }

    public static function save_input($form, $field, &$lead, $current_fields, $input_id){

        $input_name = "input_" . str_replace('.', '_', $input_id);
        $value = rgpost($input_name);

        $value = self::maybe_trim_input($value, $form["id"], $field);

        //ignore file upload when nothing was sent in the admin
        //ignore post fields in the admin
        $type = self::get_input_type($field) ;
        $multiple_files = rgar($field, "multipleFiles");
        $uploaded_files = GFFormsModel::$uploaded_files;
        $form_id = $form["id"];
        if(RG_CURRENT_VIEW == "entry" && $type == "fileupload" && ((!$multiple_files && empty($_FILES[$input_name]["name"])) || ($multiple_files && !isset($uploaded_files[$form_id][$input_name]))))
            return;
        else if(RG_CURRENT_VIEW == "entry" && in_array($field["type"], array("post_category","post_title","post_content","post_excerpt","post_tags","post_custom_field","post_image")))
            return;

        if(empty($value) && rgar($field, "adminOnly") && !IS_ADMIN){
            $value = self::get_default_value($field, $input_id);
        }

        //processing values so that they are in the correct format for each input type
        $value = self::prepare_value($form, $field, $value, $input_name, rgar($lead, "id"));

        //ignore fields that have not changed
        if($lead != null && $value === rgget($input_id, $lead)){
            return;
		}


        $lead_detail_id = self::get_lead_detail_id($current_fields, $input_id);
        self::update_lead_field_value($form, $lead, $field, $lead_detail_id, $input_id, $value);

    }

    public static function update_lead_field_value($form, $lead, $field, $lead_detail_id, $input_id, $value){
        global $wpdb;

        $lead_id = $lead["id"];
        $form_id = $form["id"];
        $lead_detail_table = self::get_lead_details_table_name();
        $lead_detail_long_table = self::get_lead_details_long_table_name();

        if(!rgblank($value)){

            $value = apply_filters("gform_save_field_value", $value, $lead, $field, $form);
            $truncated_value = GFCommon::safe_substr($value, 0, GFORMS_MAX_FIELD_LENGTH);

            if($lead_detail_id > 0){

                $result = $wpdb->update($lead_detail_table, array("value" => $truncated_value), array("id" => $lead_detail_id), array("%s"), array("%d"));
                if(false === $result)
                    return false ;

                //insert, update or delete long value
                $sql = $wpdb->prepare("SELECT count(0) FROM $lead_detail_long_table WHERE lead_detail_id=%d", $lead_detail_id);
                $has_long_field = intval($wpdb->get_var($sql)) > 0;

                //delete long field if value has been shortened
                if($has_long_field && GFCommon::safe_strlen($value) <= GFORMS_MAX_FIELD_LENGTH){
                    $sql = $wpdb->prepare("DELETE FROM $lead_detail_long_table WHERE lead_detail_id=%d", $lead_detail_id);
                    $result = $wpdb->query($sql);
                    if(false === $result)
                        return false ;
                }
                //update long field
                else if($has_long_field){
                    $result = $wpdb->update($lead_detail_long_table, array("value" => $value), array("lead_detail_id" => $lead_detail_id), array("%s"), array("%d"));
                    if(false === $result)
                        return false ;
                }
                //insert long field (value has been increased)
                else if(GFCommon::safe_strlen($value) > GFORMS_MAX_FIELD_LENGTH){
                    $result = $wpdb->insert($lead_detail_long_table, array("lead_detail_id" => $lead_detail_id, "value" => $value), array("%d", "%s"));
                    if(false === $result)
                        return false ;
                }
            }
            else{
                $result = $wpdb->insert($lead_detail_table, array("lead_id" => $lead_id, "form_id" => $form_id, "field_number" => $input_id, "value" => $truncated_value), array("%d", "%d", "%F", "%s"));
                if(false === $result)
                    return false ;

                if(GFCommon::safe_strlen($value) > GFORMS_MAX_FIELD_LENGTH){

                    //read newly created lead detal id
                    $lead_detail_id = $wpdb->insert_id;

                    //insert long value
                    $result = $wpdb->insert($lead_detail_long_table, array("lead_detail_id" => $lead_detail_id, "value" => $value), array("%d", "%s"));
                    if(false === $result)
                        return false ;
                }
            }
        }
        else{
            //Deleting long field if there is one
            $sql = $wpdb->prepare("DELETE FROM $lead_detail_long_table
                                    WHERE lead_detail_id IN(
                                        SELECT id FROM $lead_detail_table WHERE lead_id=%d AND field_number BETWEEN %s AND %s
                                    )",
                $lead_id, doubleval($input_id) - 0.001, doubleval($input_id) + 0.001);
            $result = $wpdb->query($sql);
            if(false === $result)
                return false ;

            //Deleting details for this field
            $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE lead_id=%d AND field_number BETWEEN %s AND %s ", $lead_id, doubleval($input_id) - 0.001, doubleval($input_id) + 0.001);
            $result = $wpdb->query($sql);
            if(false === $result)
                return false ;
        }
        return true;
    }

    private static function move_temp_file($form_id, $tempfile_info){

        $target = self::get_file_upload_path($form_id, $tempfile_info["uploaded_filename"]);
        $source = self::get_upload_path($form_id) . "/tmp/" . $tempfile_info["temp_filename"];

        if(rename($source, $target["path"])){
            self::set_permissions($target["path"]);
            return $target["url"];
        }
        else{
            return "FAILED (Temporary file could not be moved.)";
        }
    }

    private static function set_permissions($path){

        $permission = apply_filters("gform_file_permission", 0644, $path);
        if($permission){
           chmod($path, $permission);
        }
    }

    public static function upload_file($form_id, $file){

        $target = self::get_file_upload_path($form_id, $file["name"]);
        if(!$target){
            GFCommon::log_debug("upload_file() - FAILED (Upload folder could not be created.)");
            return "FAILED (Upload folder could not be created.)";
        }


        if(move_uploaded_file($file['tmp_name'], $target["path"])){
            GFCommon::log_debug("upload_file() - setting permissions on " . $target["path"]);
            self::set_permissions($target["path"]);
            return $target["url"];
        }
        else{
            GFCommon::log_debug("upload_file() - FAILED (Temporary file could not be copied.)");
            return "FAILED (Temporary file could not be copied.)";
        }
    }


    public static function get_upload_root(){
        $dir = wp_upload_dir();

        if($dir["error"])
            return null;

        return $dir["basedir"] . "/gravity_forms/";
    }

    public static function get_upload_url_root(){
        $dir = wp_upload_dir();

        if($dir["error"])
            return null;

        return $dir["baseurl"] . "/gravity_forms/";
    }

    public static function get_upload_path($form_id){
        return self::get_upload_root() . $form_id . "-" . wp_hash($form_id);
    }

    public static function get_upload_url($form_id){
        $dir = wp_upload_dir();

        return $dir["baseurl"] . "/gravity_forms/$form_id" . "-" . wp_hash($form_id);
    }

    public static function get_file_upload_path($form_id, $file_name){
        if (get_magic_quotes_gpc())
            $file_name = stripslashes($file_name);

        // Where the file is going to be placed
        // Generate the yearly and monthly dirs
        $time = current_time( 'mysql' );
        $y = substr( $time, 0, 4 );
        $m = substr( $time, 5, 2 );
        $target_root = self::get_upload_path($form_id) . "/$y/$m/";
        $target_root_url = self::get_upload_url($form_id) . "/$y/$m/";

        //adding filter to upload root path and url
        $upload_root_info = array("path" => $target_root, "url" => $target_root_url);
        $upload_root_info = apply_filters("gform_upload_path_{$form_id}", apply_filters("gform_upload_path", $upload_root_info, $form_id), $form_id);

        $target_root = $upload_root_info["path"];
        $target_root_url = $upload_root_info["url"];

        if(!is_dir($target_root)){
            if(!wp_mkdir_p($target_root))
                return false;

            //adding index.html files to all subfolders
            if(!file_exists(self::get_upload_root() . "/index.html")){
                GFCommon::recursive_add_index_file(self::get_upload_root());
            }
            else if(!file_exists(self::get_upload_path($form_id) . "/index.html")){
                GFCommon::recursive_add_index_file(self::get_upload_path($form_id));
            }
            else if(!file_exists(self::get_upload_path($form_id) . "/$y/index.html")){
                GFCommon::recursive_add_index_file(self::get_upload_path($form_id) . "/$y");
            }
            else{
                GFCommon::recursive_add_index_file(self::get_upload_path($form_id) . "/$y/$m");
            }

        }

        //Add the original filename to our target path.
        //Result is "uploads/filename.extension"
        $file_info = pathinfo($file_name);
        $extension = rgar($file_info, 'extension');
        if(!empty($extension))
            $extension = "." . $extension;
        $file_name = basename($file_info["basename"], $extension);

        $file_name = sanitize_file_name($file_name);

        $counter = 1;
        $target_path = $target_root . $file_name . $extension;
        while(file_exists($target_path)){
            $target_path = $target_root . $file_name . "$counter" . $extension;
            $counter++;
        }

        //Remove "." from the end if file does not have a file extension
        $target_path = trim($target_path, '.');

        //creating url
        $target_url = str_replace($target_root, $target_root_url, $target_path);

        return array("path" => $target_path, "url" => $target_url);
    }

    public static function get_tables() {
        return array(
            self::get_lead_details_long_table_name(),
            self::get_lead_notes_table_name(),
            self::get_lead_details_table_name(),
            self::get_lead_table_name(),
            self::get_form_view_table_name(),
            self::get_meta_table_name(),
            self::get_form_table_name(),
            self::get_lead_meta_table_name()
            );
    }

    public static function drop_tables(){
        global $wpdb;
        foreach(self::get_tables() as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }

    public static function mu_drop_tables($drop_tables) {
        return array_merge($drop_tables, self::get_tables());
    }

    public static function insert_form_view($form_id, $ip){
        global $wpdb;
        $table_name = self::get_form_view_table_name();

        $sql = $wpdb->prepare(" SELECT id FROM $table_name
                                WHERE form_id=%d
                                AND year(date_created) = year(utc_timestamp())
                                AND month(date_created) = month(utc_timestamp())
                                AND day(date_created) = day(utc_timestamp())
                                AND hour(date_created) = hour(utc_timestamp())", $form_id);

        $id = $wpdb->get_var($sql, 0, 0);

        if(empty($id))
            $wpdb->query($wpdb->prepare("INSERT INTO $table_name(form_id, date_created, ip) values(%d, utc_timestamp(), %s)", $form_id, $ip));
        else
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET count = count+1 WHERE id=%d", $id));
    }

    public static function is_duplicate($form_id, $field, $value){
        global $wpdb;
        $lead_detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();
        $sql_comparison = "ld.value=%s";

        switch(RGFormsModel::get_input_type($field)){
            case "time" :
                $value = sprintf("%02d:%02d %s", $value[0], $value[1], $value[2]);
            break;
            case "date" :
                $value = self::prepare_date(rgar($field, "dateFormat"), $value);
            break;
            case "number" :
                $value = GFCommon::clean_number($value, rgar($field, 'numberFormat'));
            break;
            case "phone" :
                $value = str_replace(array(")", "(", "-", " "), array("", "", "", ""), $value);
                $sql_comparison = 'replace(replace(replace(replace(ld.value, ")", ""), "(", ""), "-", ""), " ", "") = %s';
            break;
         }


        $inner_sql_template = " SELECT %s as input, ld.lead_id
                                FROM $lead_detail_table_name ld
                                INNER JOIN $lead_table_name l ON l.id = ld.lead_id
                                WHERE l.form_id=%d AND ld.form_id=%d
                                AND ld.field_number between %s AND %s
                                AND status='active' AND {$sql_comparison}";

        $sql = "SELECT count(distinct input) as match_count FROM ( ";

        $input_count = 1;
        if(is_array($field["inputs"])){
            $input_count = sizeof($field["inputs"]);
            foreach($field["inputs"] as $input){
                $union = empty($inner_sql) ? "" : " UNION ALL ";
                $inner_sql .= $union . $wpdb->prepare($inner_sql_template, $input["id"], $form_id, $form_id, $input["id"] - 0.001, $input["id"] + 0.001, $value[$input["id"]]);
            }
        }
        else{
            $inner_sql = $wpdb->prepare($inner_sql_template, $field["id"], $form_id, $form_id, doubleval($field["id"]) - 0.001, doubleval($field["id"]) + 0.001, $value);
        }

        $sql .= $inner_sql . "
                ) as count
                GROUP BY lead_id
                ORDER BY match_count DESC";

        $count = apply_filters("gform_is_duplicate_{$form_id}", apply_filters('gform_is_duplicate', $wpdb->get_var($sql), $form_id, $field, $value), $form_id, $field, $value);

        return $count != null && $count >= $input_count;
    }

    public static function get_lead($lead_id){
        global $wpdb;
        $lead_detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        $results = $wpdb->get_results($wpdb->prepare("  SELECT l.*, field_number, value
                                                        FROM $lead_table_name l
                                                        INNER JOIN $lead_detail_table_name ld ON l.id = ld.lead_id
                                                        WHERE l.id=%d", $lead_id));

        $leads = self::build_lead_array($results, true);
        return sizeof($leads) == 1 ? $leads[0] : false;
    }

    public static function get_lead_notes($lead_id){
        global $wpdb;
        $notes_table = self::get_lead_notes_table_name();

        return $wpdb->get_results($wpdb->prepare("  SELECT n.id, n.user_id, n.date_created, n.value, ifnull(u.display_name,n.user_name) as user_name, u.user_email
                                                    FROM $notes_table n
                                                    LEFT OUTER JOIN $wpdb->users u ON n.user_id = u.id
                                                    WHERE lead_id=%d ORDER BY id", $lead_id));
    }

    public static function refresh_lead_field_value($lead_id, $field_id){
        $cache_key = "GFFormsModel::get_lead_field_value_" . $lead_id . "_" . $field_id;
        GFCache::delete($cache_key);
    }

    public static function get_lead_field_value($lead, $field){

        if(empty($lead))
            return;

        //returning cache entry if available
        $cache_key = "GFFormsModel::get_lead_field_value_" . $lead["id"] . "_" . $field["id"];

        $cache_value = GFCache::get($cache_key);
        if($cache_value !== false)
            return $cache_value;

        $max_length = GFORMS_MAX_FIELD_LENGTH;
        $value = array();
        if(is_array(rgar($field, "inputs"))){
            //making sure values submitted are sent in the value even if
            //there isn't an input associated with it
            $lead_field_keys = array_keys($lead);
            foreach($lead_field_keys as $input_id){
                if(is_numeric($input_id) && absint($input_id) == absint($field["id"])){
                    $val = $lead[$input_id];
                    if(strlen($val) >= ($max_length-10)) {
                        if(empty($form))
                            $form = RGFormsModel::get_form_meta($lead["form_id"]);

                        $long_choice = self::get_field_value_long($lead, $input_id, $form);
                    }
                    else{
                        $long_choice = $val;
                    }

                     $value[$input_id] = !empty($long_choice) ? $long_choice : $val;
                }
            }
        }
        else{
            $val = rgget($field["id"], $lead);

            //To save a database call to get long text, only getting long text if regular field is "somewhat" large (i.e. max - 50)
            if(strlen($val) >= ($max_length - 50)){
                if(empty($form))
                    $form = RGFormsModel::get_form_meta($lead["form_id"]);

                $long_text = self::get_field_value_long($lead, $field["id"], $form);
            }

            $value = !empty($long_text) ? $long_text : $val;
        }

        //filtering lead value
        $value = apply_filters("gform_get_field_value", $value, $lead, $field);

        //saving value in global variable to optimize performance
        GFCache::set($cache_key, $value);

        return $value;
    }

    public static function get_field_value_long($lead, $field_number, $form, $apply_filter=true){
        global $wpdb;
        $detail_table_name = self::get_lead_details_table_name();
        $long_table_name = self::get_lead_details_long_table_name();

        $sql = $wpdb->prepare(" SELECT l.value FROM $detail_table_name d
                                INNER JOIN $long_table_name l ON l.lead_detail_id = d.id
                                WHERE lead_id=%d AND field_number BETWEEN %s AND %s", $lead["id"], doubleval($field_number) - 0.001, doubleval($field_number) + 0.001);

         $val = $wpdb->get_var($sql);

         //running aform_get_input_value when needed
         if($apply_filter){
            $field = RGFormsModel::get_field($form, $field_number);
            $input_id = (string)$field_number == (string)$field["id"] ? "" : $field_number;
            $val = apply_filters("gform_get_input_value", $val, $lead, $field, $input_id);
         }

         return $val;
    }

    public static function get_leads_by_meta($meta_key, $meta_value){
        global $wpdb;

        $sql =  $wpdb->prepare( "   SELECT l.*, d.field_number, d.value
                                    FROM {$wpdb->prefix}rg_lead l
                                    INNER JOIN {$wpdb->prefix}rg_lead_detail d ON l.id = d.lead_id
                                    INNER JOIN {$wpdb->prefix}rg_lead_meta m ON l.id = m.lead_id
                                    WHERE m.meta_key=%s AND m.meta_value=%s", $meta_key, $meta_value);

        //getting results
        $results = $wpdb->get_results($sql);
        $leads = self::build_lead_array($results);
        return $leads;
    }

    public static function get_leads($form_id, $sort_field_number=0, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false, $start_date=null, $end_date=null, $status='active', $payment_status = false){
        global $wpdb;

        if(empty($sort_field_number))
            $sort_field_number = "date_created";

        if(is_numeric($sort_field_number)) {
            $sql = self::sort_by_custom_field_query($form_id, $sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort, $status, $payment_status);
        } else {
            $sql = self::sort_by_default_field_query($form_id, $sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort, $start_date, $end_date, $status, $payment_status);
        }

        //initializing rownum
        $wpdb->query("select @rownum:=0");

        GFCommon::log_debug($sql);

        //getting results
        $results = $wpdb->get_results($sql);

        $leads = self::build_lead_array($results);

        return $leads;
    }

    public static function get_leads_count($form_id) { }

    private static function sort_by_custom_field_query($form_id, $sort_field_number=0, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false, $status='active', $payment_status = false){
        global $wpdb;
        if(!is_numeric($form_id) || !is_numeric($sort_field_number)|| !is_numeric($offset)|| !is_numeric($page_size))
            return "";

        $lead_detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        $orderby = $is_numeric_sort ? "ORDER BY query, (value+0) $sort_direction" : "ORDER BY query, value $sort_direction";
        $is_default = false;

        $search_sql = self::get_leads_where_sql(compact('form_id', 'search', 'status', 'star', 'read', 'start_date', 'end_date', 'payment_status', 'is_default'));

        $field_number_min = $sort_field_number - 0.001;
        $field_number_max = $sort_field_number + 0.001;

        $sql = "
            SELECT filtered.sort, l.*, d.field_number, d.value
            FROM $lead_table_name l
            INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
            INNER JOIN (
                SELECT distinct sorted.sort, l.id
                FROM $lead_table_name l
                INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
                INNER JOIN (
                    SELECT @rownum:=@rownum+1 as sort, id FROM (
                        SELECT 0 as query, lead_id as id, value
                        FROM $lead_detail_table_name
                        WHERE form_id=$form_id
                        AND field_number between $field_number_min AND $field_number_max

                        UNION ALL

                        SELECT 1 as query, l.id, d.value
                        FROM $lead_table_name l
                        LEFT OUTER JOIN $lead_detail_table_name d ON d.lead_id = l.id AND field_number between $field_number_min AND $field_number_max
                        WHERE l.form_id=$form_id
                        AND d.lead_id IS NULL

                    ) sorted1
                   $orderby
                ) sorted ON d.lead_id = sorted.id
                $search_sql
                LIMIT $offset,$page_size
            ) filtered ON filtered.id = l.id
            ORDER BY filtered.sort";

        return $sql;
    }

    private static function sort_by_default_field_query($form_id, $sort_field, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false, $start_date=null, $end_date=null, $status='active', $payment_status = false){
        global $wpdb;

        if(!is_numeric($form_id) || !is_numeric($offset)|| !is_numeric($page_size)){
            return "";
        }

        $lead_detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();
		$lead_meta_table_name = self::get_lead_meta_table_name();

        $where = self::get_leads_where_sql(compact('form_id', 'search', 'status', 'star', 'read', 'start_date', 'end_date', 'payment_status'));

        $entry_meta = self::get_entry_meta($form_id);
        $entry_meta_sql_join = "";
        if ( false === empty( $entry_meta ) && array_key_exists( $sort_field, $entry_meta ) ) {
            $entry_meta_sql_join = $wpdb->prepare("INNER JOIN
                                                    (
                                                    SELECT
                                                         lead_id, meta_value as $sort_field
                                                         from $lead_meta_table_name
                                                         WHERE meta_key = '$sort_field'
                                                    ) lead_meta_data ON lead_meta_data.lead_id = l.id
                                                    ");
            $is_numeric_sort = $entry_meta[$sort_field]['is_numeric'];
        }
        $grid_columns = RGFormsModel::get_grid_columns($form_id);
        if ( $sort_field != "date_created" && false === array_key_exists($sort_field, $grid_columns) )
            $sort_field = "date_created";
        $orderby = $is_numeric_sort ? "ORDER BY ($sort_field+0) $sort_direction" : "ORDER BY $sort_field $sort_direction";

		$sql = "
            SELECT filtered.sort, l.*, d.field_number, d.value
            FROM $lead_table_name l
            INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
            INNER JOIN
            (
                SELECT @rownum:=@rownum + 1 as sort, id
                FROM
                (
                    SELECT distinct l.id
                    FROM $lead_table_name l
                    INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
					$entry_meta_sql_join
                    $where
                    $orderby
                    LIMIT $offset,$page_size
                ) page
            ) filtered ON filtered.id = l.id
            ORDER BY filtered.sort";

        return $sql;
    }

    public static function get_leads_where_sql($args) {
        global $wpdb;

        extract(wp_parse_args($args, array(
            'form_id' => false,
            'search' => '',
            'status' => 'active',
            'star' => null,
            'read' => null,
            'start_date' => null,
            'end_date' => null,
            'payment_status' => null,
            'is_default' => true
            )));

        $where = array();

        if($is_default)
            $where[] = "l.form_id = $form_id";

        if($search && $is_default) {
            $where[] = $wpdb->prepare("value LIKE %s", "%$search%");
        } else if($search) {
            $where[] = $wpdb->prepare("d.value LIKE %s", "%$search%");
        }

        if($star !== null && $status == 'active')
            $where[] = $wpdb->prepare("is_starred = %d AND status = 'active'", $star);

        if($read !== null && $status == 'active')
            $where[] = $wpdb->prepare("is_read = %d AND status = 'active'", $read);

        if($payment_status)
            $where[] = $wpdb->prepare("payment_status = '%s'", $payment_status);

        if ($status !== null)
        	$where[] = $wpdb->prepare("status = %s", $status);

        if(!empty($start_date))
            $where[] = "timestampdiff(SECOND, '$start_date', date_created) >= 0";

        if(!empty($end_date))
            $where[] = "timestampdiff(SECOND, '$end_date', date_created) <= 0";

        return 'WHERE ' . implode(' AND ', $where);
    }

    public static function build_lead_array($results, $use_long_values = true){

        $leads = array();
        $lead = array();
        $form_id = 0;
        if(is_array($results) && sizeof($results) > 0){
            $form_id = $results[0]->form_id;
            $lead = array("id" => $results[0]->id, "form_id" => $results[0]->form_id, "date_created" => $results[0]->date_created, "is_starred" => intval($results[0]->is_starred), "is_read" => intval($results[0]->is_read), "ip" => $results[0]->ip, "source_url" => $results[0]->source_url, "post_id" => $results[0]->post_id, "currency" => $results[0]->currency, "payment_status" => $results[0]->payment_status, "payment_date" => $results[0]->payment_date, "transaction_id" => $results[0]->transaction_id, "payment_amount" => $results[0]->payment_amount, "payment_method" => $results[0]->payment_method, "is_fulfilled" => $results[0]->is_fulfilled, "created_by" => $results[0]->created_by, "transaction_type" => $results[0]->transaction_type, "user_agent" => $results[0]->user_agent, "status" => $results[0]->status);

            $form = RGFormsModel::get_form_meta($form_id);
            $prev_lead_id=0;
            foreach($results as $result){
                if($prev_lead_id <> $result->id && $prev_lead_id > 0){
                    array_push($leads, $lead);
                    $lead = array("id" => $result->id, "form_id" => $result->form_id,     "date_created" => $result->date_created,     "is_starred" => intval($result->is_starred),     "is_read" => intval($result->is_read),     "ip" => $result->ip,     "source_url" => $result->source_url,     "post_id" => $result->post_id,     "currency" => $result->currency,     "payment_status" => $result->payment_status,     "payment_date" => $result->payment_date,     "transaction_id" => $result->transaction_id,     "payment_amount" => $result->payment_amount,  "payment_method" => $result->payment_method,     "is_fulfilled" => $result->is_fulfilled,     "created_by" => $result->created_by,     "transaction_type" => $result->transaction_type,     "user_agent" => $result->user_agent,    "status" => $result->status);
                }

                $field_value = $result->value;
                //using long values if specified
                if($use_long_values && strlen($field_value) >= (GFORMS_MAX_FIELD_LENGTH-10)){
                    $field = RGFormsModel::get_field($form, $result->field_number);
                    $long_text = RGFormsModel::get_field_value_long($lead, $result->field_number, $form, false);
                    $field_value = !empty($long_text) ? $long_text : $field_value;
                }

                $lead[$result->field_number] = $field_value;
                $prev_lead_id = $result->id;
            }
        }


        //adding last lead.
        if(sizeof($lead) > 0)
            array_push($leads, $lead);

        //running entry through gform_get_field_value filter
        foreach($leads as &$lead){
            foreach($form["fields"] as $field){
                // skip types html, page and section?
                if(isset($field["inputs"]) && is_array($field["inputs"])){
                    foreach($field["inputs"] as $input){
                        $lead[(string)$input["id"]] = apply_filters("gform_get_input_value", rgar($lead, (string)$input["id"]), $lead, $field, $input["id"]);
                    }
                }
                else{

                    $lead[$field["id"]] = apply_filters("gform_get_input_value", rgar($lead, (string)$field["id"]), $lead, $field, "");
                }
            }
        }

		//add custom entry properties
        $entry_ids = array();
        foreach($leads as $l){
            $entry_ids[] = $l["id"];
        }
        $entry_meta = GFFormsModel::get_entry_meta($form_id);
        $meta_keys = array_keys($entry_meta);
        $entry_meta_data_rows = gform_get_meta_values_for_entries($entry_ids, $meta_keys);
        foreach($leads as &$lead){
            foreach($entry_meta_data_rows as $entry_meta_data_row){
                if($entry_meta_data_row->lead_id == $lead["id"]){
                    foreach($meta_keys as $meta_key){
                        $lead[$meta_key] = $entry_meta_data_row->$meta_key;
                    }
                }
            }
        }

        return $leads;
    }

    public static function save_key($key){
        $current_key = get_option("rg_gforms_key");
        if(empty($key)){
            delete_option("rg_gforms_key");
        }
        else if($current_key != $key){
            $key = trim($key);
            update_option("rg_gforms_key", md5($key));
        }
    }

    public static function get_lead_count($form_id, $search, $star=null, $read=null, $start_date=null, $end_date=null, $status=null, $payment_status = null){
        global $wpdb;

        if(!is_numeric($form_id))
            return "";

        $detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        $where = self::get_leads_where_sql(compact('form_id', 'search', 'status', 'star', 'read', 'start_date', 'end_date', 'payment_status', 'is_default'));

        $sql = "SELECT count(distinct l.id)
                FROM $lead_table_name l
                INNER JOIN $detail_table_name ld ON l.id = ld.lead_id
                $where";

        return $wpdb->get_var($sql);
    }

    public static function get_lead_ids($form_id, $search, $star=null, $read=null, $start_date=null, $end_date=null, $status=null, $payment_status = null){
        global $wpdb;

        if(!is_numeric($form_id))
            return "";

        $detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        $where = self::get_leads_where_sql(compact('form_id', 'search', 'status', 'star', 'read', 'start_date', 'end_date', 'payment_status', 'is_default'));

        $sql = "SELECT distinct l.id
                FROM $lead_table_name l
                INNER JOIN $detail_table_name ld ON l.id = ld.lead_id
                $where";

        $rows =  $wpdb->get_results($sql);

        if(empty($rows))
            return array();

        foreach ($rows as $row){
            $lead_ids[] = $row->id;
        }

        return $lead_ids;

    }

    public static function get_grid_columns($form_id, $input_label_only=false){
        $form = self::get_form_meta($form_id);
        $field_ids = self::get_grid_column_meta($form_id);

        if(!is_array($field_ids)){
            $field_ids = array();
            for($i=0, $count=sizeof($form["fields"]); $i<$count && $i<5; $i++){
                $field = $form["fields"][$i];

                //loading post category fields with choices and inputs
                if($field["type"] == "post_category")
                    $field = GFCommon::add_categories_as_choices($field, "");

                if(RGForms::get("displayOnly",$field) || self::get_input_type($field) == "list")
                    continue;

                if(isset($field["inputs"]) && is_array($field["inputs"])){
                    if($field["type"] == "name"){
                        $field_ids[] = $field["id"] . '.3'; //adding first name
                        $field_ids[] = $field["id"] . '.6'; //adding last name
                    }
                    else{
                        $field_ids[] = $field["inputs"][0]["id"]; //getting first input
                    }
                }
                else{
                    $field_ids[] = $field["id"];
                }
            }
			//adding default entry meta columns
            $entry_metas = GFFormsModel::get_entry_meta($form_id);
            foreach ($entry_metas as $key => $entry_meta){
            	if (rgar($entry_meta,"is_default_column"))
            		$field_ids[] = $key;
            }
        }

        $columns = array();
        $entry_meta = self::get_entry_meta($form_id);
        foreach($field_ids as $field_id){

            switch($field_id){
                case "id" :
                    $columns[$field_id] = array("label" => "Entry Id", "type" => "id");
                break;
                case "ip" :
                    $columns[$field_id] = array("label" => "User IP", "type" => "ip");
                break;
                case "date_created" :
                    $columns[$field_id] = array("label" => "Entry Date", "type" => "date_created");
                break;
                case "source_url" :
                    $columns[$field_id] = array("label" => "Source Url", "type" => "source_url");
                break;
                case "payment_status" :
                    $columns[$field_id] = array("label" => "Payment Status", "type" => "payment_status");
                break;
                case "transaction_id" :
                    $columns[$field_id] = array("label" => "Transaction Id", "type" => "transaction_id");
                break;
                case "payment_date" :
                    $columns[$field_id] = array("label" => "Payment Date", "type" => "payment_date");
                break;
                case "payment_amount" :
                    $columns[$field_id] = array("label" => "Payment Amount", "type" => "payment_amount");
                break;
                case "created_by" :
                    $columns[$field_id] = array("label" => "User", "type" => "created_by");
                break;
				case ((is_string($field_id) || is_int($field_id)) && array_key_exists($field_id, $entry_meta)) :
                    $columns[$field_id] = array("label" => $entry_meta[$field_id]["label"], "type" => $field_id);
                break;
                default :
                    $field = self::get_field($form, $field_id);
                    if($field)
                        $columns[strval($field_id)] = array("label" => self::get_label($field, $field_id, $input_label_only), "type" => rgget("type", $field), "inputType" => rgget("inputType", $field));
            }
        }
        return $columns;
    }

    public static function get_label($field, $input_id = 0, $input_only = false){
        $field_label = (IS_ADMIN || RG_CURRENT_PAGE == "select_columns.php" || RG_CURRENT_PAGE == "print-entry.php" || rgget("gf_page", $_GET) == "select_columns" || rgget("gf_page", $_GET) == "print-entry") && !rgempty("adminLabel", $field) ? rgar($field,"adminLabel") : rgar($field,"label");
        $input = self::get_input($field, $input_id);
        if(rgget("type", $field) == "checkbox" && $input != null)
            return $input["label"];
        else if($input != null)
            return $input_only ? $input["label"] : $field_label . ' (' . $input["label"] . ')';
        else
            return $field_label;
    }

    public static function get_input($field, $id){
        if(isset($field["inputs"]) && is_array($field["inputs"])){
            foreach($field["inputs"] as $input)
            {
                if($input["id"] == $id)
                    return $input;
            }
        }
        return null;
    }

    public static function has_input($field, $input_id){
        if(!is_array($field["inputs"]))
            return false;
        else{
            foreach($field["inputs"] as $input)
            {
                if($input["id"] == $input_id)
                    return true;
            }
            return false;
        }
    }

    public static function get_current_page_url($force_ssl=false) {
        $pageURL = 'http';
        if (RGForms::get("HTTPS",$_SERVER) == "on" || $force_ssl)
            $pageURL .= "s";
        $pageURL .= "://";

        $pageURL .= RGForms::get("HTTP_HOST", $_SERVER) . rgget("REQUEST_URI", $_SERVER);
        return $pageURL;
    }

    public static function get_submitted_fields($form_id){
        global $wpdb;
        $lead_detail_table_name = self::get_lead_details_table_name();
        $field_list = "";
        $fields = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT field_number FROM $lead_detail_table_name WHERE form_id=%d", $form_id));
        foreach($fields as $field)
            $field_list .= intval($field->field_number) . ',';

        if(!empty($field_list))
            $field_list = substr($field_list, 0, strlen($field_list) -1);

        return $field_list;
    }

    public static function get_field($form, $field_id){
        if(is_numeric($field_id))
            $field_id = intval($field_id); //removing floating part of field (i.e 1.3 -> 1) to return field by input id

        if(!is_array($form["fields"]))
            return null;

        global $_fields;
        $key = $form["id"] . "_" . $field_id;
        if(!isset($_fields[$key])){
            $_fields[$key] = null;
            foreach($form["fields"] as $field){
                if($field["id"] == $field_id){
                    $_fields[$key] = $field;
                    break;
                }
            }
        }
        return $_fields[$key];
    }

    public static function is_html5_enabled(){
        return get_option("rg_gforms_enable_html5");
    }

    /**
    * Return the current lead being processed. Should only be called when a form has been submitted.
    * If called before the "real" lead has been saved to the database, uses self::create_lead() to create
    * a temporary lead to work with.
    */
    public static function get_current_lead() {

        // if a GF submission is not in process, always return false
        if(!rgpost('gform_submit'))
            return false;

        if(!self::$_current_lead) {
            $form = self::get_form_meta(rgpost('gform_submit'));
            self::$_current_lead = self::create_lead($form);
        }

        return self::$_current_lead;
    }

    /**
    * Set RGFormsModel::$lead for use in hooks where $lead is not explicitly passed.
    *
    * @param mixed $lead
    */
    public static function set_current_lead($lead) {
        GFCache::flush();
        self::$_current_lead = $lead;
    }

    /**
    * v1.7 introduces conditional confirmations. If the form's "confirmations" key is empty, grab the existing confirmation
    * and populate it in the form's "confirmations" property.
    *
    * @param mixed $form
    */
    public static function convert_confirmation($form) {

        $id = uniqid();

        // convert confirmation to new confirmations format
        $confirmation = rgar($form,'confirmation');
        $confirmation['id'] = $id;
        $confirmation['name'] = __('Default Confirmation', 'gravityforms');
        $confirmation['isDefault'] = true;

        $form['confirmations'] = array($id => $confirmation);

        self::save_form_confirmations($form['id'], $form['confirmations']);

        return $form;
    }

    public static function load_confirmations($form) {

        $confirmations = self::get_form_confirmations($form['id']);

        // if there are no confirmations, convert existing (singular) confirmation (prior to 1.7) to new (plural) confirmations format
        if(empty($confirmations)) {
            $form = self::convert_confirmation($form);
        } else {
            $form['confirmations'] = $confirmations;
        }

        return $form;
    }

    public static function get_form_confirmations($form_id) {
        global $wpdb;

        if(isset($_confirmations[$form_id]))
            return $_confirmations[$form_id];

        $tablename = GFFormsModel::get_meta_table_name();
        $sql = $wpdb->prepare("SELECT confirmations FROM $tablename WHERE form_id = %d", $form_id);
        $results = $wpdb->get_results($sql, ARRAY_A);
        $confirmations = rgars($results, '0/confirmations');

        self::$_confirmations[$form_id] = $confirmations ? self::unserialize($confirmations) : array();

        return self::$_confirmations[$form_id];
    }

    public static function save_form_confirmations($form_id, $confirmations) {
        return self::update_form_meta($form_id, $confirmations, 'confirmations');
    }

    public static function save_form_notifications($form_id, $notifications) {
        return self::update_form_meta($form_id, $notifications, 'notifications');
    }

    public static function get_form_ids($active = true, $trash = false){
        global $wpdb;
        $table = self::get_form_table_name();
        $sql = $wpdb->prepare("SELECT id from $table where is_active = %d and is_trash = %d", (bool) $active, (bool) $trash );
        $results = $wpdb->get_col($sql);
        return $results;
    }

    public static function get_entry_meta($form_ids){
        global $_entry_meta;

        if($form_ids==0)
            $form_ids = self::get_form_ids();

        if(!is_array($form_ids))
            $form_ids=array($form_ids);
        $meta = array();
        foreach($form_ids as $form_id){
            if(!isset($_entry_meta[$form_id])){
                $_entry_meta = array();
                $_entry_meta[$form_id] = apply_filters('gform_entry_meta', array(), $form_id);
            }
            $meta = array_merge($meta, $_entry_meta[$form_id]);
        }

        return $meta;
    }

    public static function set_entry_meta($lead, $form){
        $entry_meta = self::get_entry_meta($form["id"]);
        $keys = array_keys($entry_meta);
        foreach ($keys as $key){
            if (isset($entry_meta[$key]['update_entry_meta_callback'])){
                $callback = $entry_meta[$key]['update_entry_meta_callback'];
                $value = call_user_func_array($callback, array($key, $lead, $form));
                gform_update_meta($lead["id"], $key, $value);
                $lead[$key] = $value;
            }
        }
        return $lead;
    }

    public static function search_leads($form_id, $search_criteria = array(), $sorting = null, $paging = null) {

        global $wpdb;
        $sort_field = isset($sorting["key"]) ? $sorting["key"] : "date_created"; // column, field or entry meta

        if (is_numeric($sort_field))
            $sql = self::sort_by_field_query($form_id, $search_criteria, $sorting, $paging);
        else
            $sql = self::sort_by_column_query($form_id, $search_criteria, $sorting, $paging);

        //initializing rownum
        $wpdb->query("select @rownum:=0");

        GFCommon::log_debug($sql);

        //getting results
        $results = $wpdb->get_results($sql);

        $leads = GFFormsModel::build_lead_array($results);

        return $leads;
    }

    public static function search_lead_ids($form_id, $search_criteria = array()) {
        global $wpdb;

        $detail_table_name = GFFormsModel::get_lead_details_table_name();
        $lead_table_name   = GFFormsModel::get_lead_table_name();

        $where = self::get_search_where($form_id, $search_criteria);

        $sql = "SELECT distinct l.id
                FROM $lead_table_name l
                INNER JOIN $detail_table_name ld ON l.id = ld.lead_id
                $where
                ";

        $rows =  $wpdb->get_results($sql);

        if(empty($rows))
            return array();

        foreach ($rows as $row){
            $lead_ids[] = $row->id;
        }

        return $lead_ids;
    }

    private static function sort_by_field_query($form_id, $search_criteria, $sorting, $paging) {
        global $wpdb;
        $sort_field_number = rgar($sorting, "key");
        $sort_direction    = isset($sorting["direction"]) ? $sorting["direction"] : "DESC";

        $is_numeric_sort = isset($sorting["is_numeric"]) ? $sorting["is_numeric"] : false;
        $offset          = isset($paging["offset"]) ? $paging["offset"] : 0;
        $page_size       = isset($paging["page_size"]) ? $paging["page_size"] : 20;

        if (!is_numeric($sort_field_number) || !is_numeric($offset) || !is_numeric($page_size))
            return "";

        $lead_detail_table_name = GFFormsModel::get_lead_details_table_name();
        $lead_table_name        = GFFormsModel::get_lead_table_name();

        $orderby = $is_numeric_sort ? "ORDER BY query, (value+0) $sort_direction" : "ORDER BY query, value $sort_direction";


        $field_filters_where = self::get_field_filters_where($form_id, $search_criteria);
        if (!empty($field_filters_where))
            $where_arr[]=$field_filters_where;

        $info_search_where = self::get_info_search_where($search_criteria);
        if(!empty($info_search_where))
            $where_arr[]  = $info_search_where;

        $search_operator = self::get_search_operator($search_criteria);
        $where = empty($where_arr) ? "" : join(" $search_operator ", $where_arr);

        $date_range_where = self::get_date_range_where($search_criteria);
        if(!empty($date_range_where))
            $where = empty($where) ? $date_range_where : $where . " AND " . $date_range_where;

        if(!empty($where))
            $where = "WHERE " . $where;

        if(is_array($form_id)){
            $in_str_arr = array_fill(0, count($form_id), '%s');
            $in_str     = esc_sql(join(",", $in_str_arr));
            $form_id_where = $wpdb->prepare(" AND form_id IN ($in_str)", $form_id);
        } else {
            $form_id_where = $form_id > 0 ? $wpdb->prepare(" AND l.form_id=%d", $form_id) : "";
        }

        $field_number_min = $sort_field_number - 0.001;
        $field_number_max = $sort_field_number + 0.001;

        $sql = "
            SELECT filtered.sort, l.*, d.field_number, d.value
            FROM $lead_table_name l
            INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
            INNER JOIN (
                SELECT distinct sorted.sort, l.id
                FROM $lead_table_name l
                INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
                INNER JOIN (
                    SELECT @rownum:=@rownum+1 as sort, id FROM (
                        SELECT 0 as query, lead_id as id, value
                        FROM $lead_detail_table_name l
                        WHERE field_number between $field_number_min AND $field_number_max
                        $form_id_where

                        UNION ALL

                        SELECT 1 as query, l.id, d.value
                        FROM $lead_table_name l
                        LEFT OUTER JOIN $lead_detail_table_name d ON d.lead_id = l.id AND field_number between $field_number_min AND $field_number_max
                        WHERE d.lead_id IS NULL
                        $form_id_where

                    ) sorted1
                   $orderby
                ) sorted ON d.lead_id = sorted.id
                $where
                LIMIT $offset,$page_size
            ) filtered ON filtered.id = l.id

            ORDER BY filtered.sort";

        return $sql;
    }

    private static function sort_by_column_query($form_id, $search_criteria, $sorting, $paging) {
        global $wpdb;
        $sort_field      = isset($sorting["key"]) ? $sorting["key"] : "date_created";
        $sort_direction  = isset($sorting["direction"]) ? $sorting["direction"] : "DESC";
        $is_numeric_sort = isset($sorting["is_numeric"]) ? $sorting["is_numeric"] : false;
        $offset          = isset($paging["offset"]) ? $paging["offset"] : 0;
        $page_size       = isset($paging["page_size"]) ? $paging["page_size"] : 20;

        if (!is_numeric($offset) || !is_numeric($page_size)) {
            return "";
        }

        $lead_detail_table_name = GFFormsModel::get_lead_details_table_name();
        $lead_table_name        = GFFormsModel::get_lead_table_name();
        $lead_meta_table_name   = GFFormsModel::get_lead_meta_table_name();

        $entry_meta          = GFFormsModel::get_entry_meta(is_array($form_id) ? 0 : $form_id);
        $entry_meta_sql_join = "";
        $sort_field_is_entry_meta = false;
        if (false === empty($entry_meta) && array_key_exists($sort_field, $entry_meta)) {
            $entry_meta_sql_join = $wpdb->prepare("INNER JOIN
                                                    (
                                                    SELECT
                                                         lead_id, meta_value as $sort_field
                                                         from $lead_meta_table_name
                                                         WHERE meta_key=%s
                                                    ) lead_meta_data ON lead_meta_data.lead_id = l.id
                                                    ", $sort_field);
            $is_numeric_sort = $entry_meta[$sort_field]['is_numeric'];
            $sort_field_is_entry_meta = true;
        } else {
            $db_columns = self::get_lead_db_columns();
            if ($sort_field != "date_created" && false === in_array($sort_field, $db_columns))
                $sort_field = "date_created";
        }

        if($sort_field_is_entry_meta){
            $orderby = $is_numeric_sort ? "ORDER BY ($sort_field+0) $sort_direction" : "ORDER BY $sort_field $sort_direction";
        } else {
            $orderby = $is_numeric_sort ? "ORDER BY (l.$sort_field+0) $sort_direction" : "ORDER BY l.$sort_field $sort_direction";
        }

        $where = self::get_search_where($form_id, $search_criteria);

        $sql  = "
            SELECT filtered.sort, l.*, d.field_number, d.value
            FROM $lead_table_name l
            INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
            INNER JOIN
            (
                SELECT @rownum:=@rownum + 1 as sort, id
                FROM
                (
                    SELECT distinct l.id
                    FROM $lead_table_name l
                    INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
                    $entry_meta_sql_join
					$where
                    $orderby
                    LIMIT $offset,$page_size
                ) page
            ) filtered ON filtered.id = l.id

            ORDER BY filtered.sort";

        return $sql;
    }

    private static function get_search_where($form_id, $search_criteria){

        global $wpdb;
        $where_arr = array();

        $field_filters_where = self::get_field_filters_where($form_id, $search_criteria);
        if (!empty($field_filters_where))
            $where_arr[]=$field_filters_where;

        $info_search_where = self::get_info_search_where($search_criteria);

        if(!empty($info_search_where))
            $where_arr[] = $info_search_where;

        if(is_array($form_id)){
            $in_str_arr = array_fill(0, count($form_id), '%s');
            $in_str     = esc_sql(join(",", $in_str_arr));
            $form_id_where = $wpdb->prepare("l.form_id IN ($in_str)", $form_id);
        } else {
            $form_id_where = $form_id > 0 ? $wpdb->prepare("l.form_id=%d", $form_id) : "";
        }

        $search_operator = self::get_search_operator($search_criteria);
        $where = empty($where_arr) ? "" : "(" . join( " $search_operator ", $where_arr) . ")" ;

        $date_range_where = self::get_date_range_where($search_criteria);

        $where_and_clause_arr = array();
        if(!empty($date_range_where))
            $where_and_clause_arr[] = $date_range_where;

        if(!empty($form_id_where))
            $where_and_clause_arr[] = $form_id_where;

        $status_where = isset($search_criteria["status"]) ?  $wpdb->prepare("l.status = %s", $search_criteria["status"]) : "";
        if(!empty($status_where))
            $where_and_clause_arr[] = $status_where;

        $where_and_clause = join(" AND ", $where_and_clause_arr);

        if(!empty($where_and_clause))
            $where_and_clause = "(" . $where_and_clause . ")";

        $where_parts = array();
        if(!empty($where))
            $where_parts[] = $where;
        if(!empty($where_and_clause))
            $where_parts[] = $where_and_clause;

        $where = join(" AND ", $where_parts);

        if(!empty($where))
            $where = "WHERE " . $where;

        return $where;
    }

    private static function get_field_filters_where($form_id, $search_criteria) {
        global $wpdb;

        $field_filters = rgar($search_criteria, "field_filters");

        $search_operator = self::get_search_operator($search_criteria);

        if(empty($field_filters))
            return false;

        unset($field_filters["mode"]);

        $sql_array               = array();
        $lead_details_table_name = GFFormsModel::get_lead_details_table_name();
        $lead_meta_table_name    = GFFormsModel::get_lead_meta_table_name();
        if(is_array($form_id)){
            $in_str_arr = array_fill(0, count($form_id), '%s');
            $in_str     = esc_sql(join(",", $in_str_arr));
            $form_id_where = $wpdb->prepare("AND form_id IN ($in_str)", $form_id);
        } else {
            $form_id_where = $form_id > 0 ? $wpdb->prepare("AND form_id=%d", $form_id) : "";
        }
        $info_column_keys = self::get_lead_db_columns();
        $entry_meta = self::get_entry_meta(is_array($form_id) ? 0 : $form_id);
        array_push($info_column_keys, "id");
        foreach ($field_filters as $search) {

            $key = rgar($search, "key");
            if("entry_id" === $key)
                $key = "id";
            if(in_array($key, $info_column_keys))
                continue;

            $val = rgar($search, "value");
            $operator    = isset($search["operator"]) ? strtolower($search["operator"]) : "=";
            if("is" == $operator)
                $operator = "=";
            if("isnot" == $operator)
                $operator = "<>";
            if("contains" == $operator)
                $operator = "like";
            $search_term  = "like" == $operator ? "%$val%" : $val;

            $search_type = rgar($search, "type");
            if(empty($search_type)){
                if(empty($key)){
                    $search_type = "global";
                } elseif (is_numeric($key)){
                    $search_type = "field";
                } elseif(isset($entry_meta[$key])){
                    $search_type = "meta";
                } else {
                    $search_type = "global";
                }
            }

            switch ($search_type) {
                case "field":
                    $upper_field_number_limit = (string)(int)$key === $key ? (float)$key + 0.9999 : (float)$key + 0.0001;
                    /* doesn't support "<>" for checkboxes */
                    $field_query = $wpdb->prepare("l.id IN
									(
									SELECT
									lead_id
									from $lead_details_table_name
									WHERE (field_number BETWEEN %s AND %s AND value $operator %s)
									$form_id_where
									)
								", (float)$key - 0.0001, $upper_field_number_limit, $search_term);
                    if(empty($val) || "%%" === $val || "<>" === $operator){
                        $skipped_field_query = $wpdb->prepare("l.id NOT IN
									(
									SELECT
									lead_id
									from $lead_details_table_name
									WHERE (field_number BETWEEN %s AND %s)
									$form_id_where
									)
								", (float)$key - 0.0001, $upper_field_number_limit, $search_term);
                        $field_query = "(" . $field_query . " OR " . $skipped_field_query . ")";
                    }

                    $sql_array[] = $field_query;

                    /*
                    //supports "<>" for checkboxes but it doesn't scale
                    $sql_array[] = $wpdb->prepare("l.id IN
                                    (SELECT lead_id
                                    FROM
                                        (
                                            SELECT lead_id, value
                                            FROM $lead_details_table_name
                                            WHERE form_id = %d
                                            AND (field_number BETWEEN %s AND %s)
                                            GROUP BY lead_id
                                            HAVING value $operator %s
                                        ) ld
                                    )
                                    ", $form_id, (float)$key - 0.0001, $upper_field_number_limit, $val );
                    */
                    break;
                case "global":

                    $sql_array[] = $wpdb->prepare("value $operator %s", $search_term);
                    break;
                case "meta":
                    /* doesn't support "<>" for multiple values of the same key */

                    $meta = rgar($entry_meta, $key);
                    $placeholder = rgar($meta, "is_numeric") ? "%s" : "%s";
                    $search_term = "like" == $operator ? "%$val%" : $val;
                    $sql_array[] = $wpdb->prepare("l.id IN
									(
									SELECT
									lead_id
									FROM $lead_meta_table_name
									WHERE meta_key=%s AND meta_value $operator $placeholder
									$form_id_where
									)
								", $search["key"], $search_term);
                    break;

            }

        }

        $sql = empty($sql_array) ? "" : join(" " . $search_operator . " ", $sql_array);

        return $sql;
    }

    private static function get_date_range_where($search_criteria) {
        global $wpdb;

        if(isset($search_criteria["start_date"])){
            $where_array[] = $wpdb->prepare("date_created >= %s", $search_criteria["start_date"]);
        }

        if(isset($search_criteria["end_date"])){

            $end_date = new DateTime($search_criteria["end_date"]);
            $end_datetime_str = $end_date->format('Y-m-d H:i:s');
            $end_date_str = $end_date->format('Y-m-d');

            // extend end date till the end of the day unless a time was specified. 00:00:00 is ignored.
            if($end_datetime_str == $end_date_str . " 00:00:00" ){
                $end_date =  $end_date->format('Y-m-d') . " 23:59:59";
            } else {
                $end_date =  $end_date->format('Y-m-d H:i:s');
            }

            $where_array[] = $wpdb->prepare("date_created <= %s", $end_date);
        }


        $sql = empty($where_array) ? "" : "(" . join(" AND ", $where_array) . ")";

        return $sql;
    }

    private static function get_search_operator($search_criteria){
        if(!isset($search_criteria["field_filters"]))
            return "";
        $field_filters = $search_criteria["field_filters"];

        $search_mode = isset($field_filters["mode"]) ? strtolower($field_filters["mode"]) : "all";

        return strtolower($search_mode) == "any" ? "OR" : "AND";
    }

    private static function get_lead_db_columns(){
        return array("id", "form_id", "post_id", "date_created", "is_starred", "is_read", "ip", "source_url", "user_agent", "currency", "payment_status", "payment_date", "payment_amount", "transaction_id", "is_fulfilled", "created_by", "transaction_type", "status");
    }

    private static function get_info_search_where($search_criteria) {
        global $wpdb;

        $field_filters = rgar($search_criteria, "field_filters");
        $search_operator = self::get_search_operator($search_criteria);

        if(empty($field_filters))
            return;

        unset($field_filters["mode"]);

        $info_column_keys = self::get_lead_db_columns();
        array_push($info_column_keys, "id");
        $int_columns = array("id", "post_id", "is_starred", "is_read", "is_fulfilled");
        $where_array = array();
        foreach ($field_filters as $filter) {
            $key = strtolower(rgar($filter, "key"));
            if(!in_array($key, $info_column_keys))
                continue;
            if("entry_id" === $key)
                $key = "id";

            $operator    = isset($filter["operator"]) ? strtolower($filter["operator"]) : "=";

            $value = rgar($filter, "value");

            if("is" == $operator)
                $operator = "=";
            if("isnot" == $operator)
                $operator = "<>";
            if("contains" == $operator)
                $operator = "like";
            $search_term   = "like" == $operator ? "%value%" : $value;
            if("date_created" == $key && "=" === $operator){
                $where_array[] = $wpdb->prepare("(datediff(date_created, %s) >= 0 AND datediff(date_created, %s) <= 0)", $search_term, $search_term);
            }else if(in_array($key, $int_columns)){
                $where_array[] = $wpdb->prepare("l.{$key} $operator %d", $search_term);
            } else {
                $where_array[] = $wpdb->prepare("l.{$key} $operator %s", $search_term);
            }

        }


        $sql = empty($where_array) ? "" : join(" $search_operator ", $where_array);

        return $sql;
    }

    public static function count_search_leads($form_id, $search_criteria = array()) {
        global $wpdb;

        $detail_table_name = GFFormsModel::get_lead_details_table_name();
        $lead_table_name   = GFFormsModel::get_lead_table_name();

        $where = self::get_search_where($form_id, $search_criteria);

        $sql = "SELECT count(distinct l.id)
                FROM $lead_table_name l
                INNER JOIN $detail_table_name ld ON l.id = ld.lead_id
                $where
                ";

        return $wpdb->get_var($sql);
    }

    public static function get_lead_count_all_forms($status='active'){
		global $wpdb;

	    $detail_table_name = self::get_lead_details_table_name();
	    $lead_table_name = self::get_lead_table_name();

	    $sql = "SELECT count(id)
	            FROM $lead_table_name
	            WHERE status='{$status}'";

	     return $wpdb->get_var($sql);
	}

    public static function dbDelta($sql){
        global $wpdb;

        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

        //Fixes issue with dbDelta lower-casing table names, which cause problems on case sensitive DB servers.
        add_filter( 'dbdelta_create_queries', array("GFForms", "dbdelta_fix_case"));

        dbDelta($sql);

        remove_filter('dbdelta_create_queries', array("GFForms", "dbdelta_fix_case"));
    }

    public static function get_db_charset(){
        global $wpdb;

        if ( ! empty($wpdb->charset) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

        if ( ! empty($wpdb->collate) )
            $charset_collate .= " COLLATE $wpdb->collate";

        return $charset_collate;
    }

    /**
     * Trims values inside choice texts, choice values, input labels, field labels and field conditionalLogic
     *
     * @param array $form Form object.
     * @param bool $form_updated Output parameter.
     * @return array $form
     */
    public static function trim_form_meta_values($form, &$form_updated = false){
        $form_id = $form["id"];
        GFCommon::log_debug("Starting GFFormsModel::trim_form_meta_values()");
        if(isset($form["fields"]) && is_array($form["fields"])){
            foreach($form["fields"] as &$field){
                $trim_value = apply_filters("gform_trim_input_value", true, $form_id, $field);
                if(!$trim_value){
                    continue;
                }

                if(isset($field["label"]) && $field["label"] != trim($field["label"])){
                    $field["label"] = trim($field["label"]);
                    $form_updated = true;
                }
                if(isset($field["choices"]) && is_array($field["choices"])){
                    foreach($field["choices"] as &$choice){
                        if(isset($choice["text"]) && $choice["text"] != trim($choice["text"])){
                            $choice["text"] = trim($choice["text"]);
                            $form_updated = true;
                        }
                        if(isset($choice["value"]) && $choice["value"] != trim($choice["value"])){
                            $choice["value"] = trim($choice["value"]);
                            $form_updated = true;
                        }
                    }
                }
                if(isset($field["inputs"]) && is_array($field["inputs"])){
                    foreach($field["inputs"] as &$input){
                        if(isset($input["label"]) && $input["label"] != trim($input["label"])){
                            $input["label"] = trim($input["label"]);
                            $form_updated = true;
                        }
                    }
                }
            }
            $form["fields"] = GFFormsModel::trim_conditional_logic_values($form["fields"], $form, $form_updated);
        }
        if($form_updated){
            GFCommon::log_debug("Trimmed form values");
        }
        return $form;
    }

    /**
     * Trims values from an array of elements e.g. notifications and confirmations
     *
     * @param array $meta_array Form object.
     * @param array $form Form object.
     * @param bool $updated Output parameter.
     * @return array $meta_array
     */
    public static function trim_conditional_logic_values($meta_array, $form, &$updated = false){
        GFCommon::log_debug("Starting GFFormsModel::trim_conditional_logic_values()");
        if(is_array($meta_array)){
            foreach($meta_array as &$meta){
                $meta = self::trim_conditional_logic_values_from_element($meta, $form, $updated);
            }
        }
        if($updated){
            GFCommon::log_debug("Trimmed conditional logic values");
        }
        return $meta_array;
    }

    /**
     * Trims values from elements e.g. fields, notifications and confirmations
     *
     * @param array $element Form object.
     * @param array $form Form object.
     * @param bool $updated Output parameter.
     * @return array $element
     */
    public static function trim_conditional_logic_values_from_element($element, $form = array(), &$updated = false){
        if(isset($element["conditionalLogic"]) && is_array($element["conditionalLogic"]) && isset($element["conditionalLogic"]["rules"]) && is_array($element["conditionalLogic"]["rules"])){
            foreach($element["conditionalLogic"]["rules"] as &$rule){
                $value = (string) $rule["value"];
                if($value !== trim($value)){
                    $field = isset($form["fields"]) ? GFFormsModel::get_field($form, $rule["fieldId"]) : array();
                    $trim_value = apply_filters("gform_trim_input_value", true, rgar($form, "id"), $field);
                    if($trim_value){
                        $rule["value"] = trim($rule["value"]);
                        $updated = true;
                    }

                }
            }
        }
        return $element;
    }
}

class RGFormsModel extends GFFormsModel { }

global $_gform_lead_meta;
$_gform_lead_meta = array();

//functions to handle lead meta
function gform_get_meta($entry_id, $meta_key){
    global $wpdb, $_gform_lead_meta;

    //get from cache if available
    $cache_key = $entry_id . "_" . $meta_key;
    if(array_key_exists($cache_key, $_gform_lead_meta))
        return $_gform_lead_meta[$cache_key];

    $table_name = RGFormsModel::get_lead_meta_table_name();
    $results = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$table_name} WHERE lead_id=%d AND meta_key=%s", $entry_id, $meta_key));
    $value = isset($results[0]) ? $results[0]->meta_value : null;
    $meta_value = $value == null ? false : maybe_unserialize($value);
    $_gform_lead_meta[$cache_key] = $meta_value;
    return $meta_value;
}

function gform_get_meta_values_for_entries($entry_ids, $meta_keys){
    global $wpdb;

    if (empty($meta_keys) || empty($entry_ids))
        return array();

    $table_name = RGFormsModel::get_lead_meta_table_name();
    $meta_value_array = array();
    $select_meta_keys = join(",", $meta_keys);
    $meta_key_select_array = array();

    foreach($meta_keys as $meta_key){
        $meta_key_select_array[] = "max(case when meta_key = '$meta_key' then meta_value end) as $meta_key";
    }

    $entry_ids_str = join(",", $entry_ids);

    $meta_key_select = join(",", $meta_key_select_array);

    $sql_query = "  SELECT
                    lead_id, $meta_key_select
                    FROM $table_name
                    WHERE lead_id IN ($entry_ids_str)
                    GROUP BY lead_id";

    $results = $wpdb->get_results($sql_query);

    foreach($results as $result){
        foreach($meta_keys as $meta_key){
            $result->$meta_key = $result->$meta_key == null ? false : maybe_unserialize($result->$meta_key);
        }
    }

    $meta_value_array = $results;
    return $meta_value_array;
}

function gform_update_meta($entry_id, $meta_key, $meta_value){
    global $wpdb, $_gform_lead_meta;
    $table_name = RGFormsModel::get_lead_meta_table_name();
	if (false === $meta_value)
         $meta_value = "0";
    $meta_value = maybe_serialize($meta_value);
    $meta_exists = gform_get_meta($entry_id, $meta_key) !== false;
    if($meta_exists){
        $wpdb->update($table_name, array("meta_value" => $meta_value), array("lead_id" => $entry_id, "meta_key" => $meta_key),array("%s"), array("%d", "%s"));
    }
    else{
        $lead_table_name = RGFormsModel::get_lead_table_name();
		$form_id = $wpdb->get_var($wpdb->prepare("SELECT form_id from $lead_table_name WHERE id=%d", $entry_id));
		$wpdb->insert($table_name, array("form_id" => $form_id, "lead_id" => $entry_id, "meta_key" => $meta_key, "meta_value" => $meta_value), array("%d", "%d", "%s", "%s"));
    }

    //updates cache
    $cache_key = $entry_id . "_" . $meta_key;
    if(array_key_exists($cache_key, $_gform_lead_meta))
        $_gform_lead_meta[$cache_key] = maybe_unserialize($meta_value);
}

function gform_delete_meta($entry_id, $meta_key=""){
    global $wpdb, $_gform_lead_meta;
    $table_name = RGFormsModel::get_lead_meta_table_name();
    $meta_filter = empty($meta_key) ? "" : $wpdb->prepare("AND meta_key=%s", $meta_key);

    $wpdb->query($wpdb->prepare("DELETE FROM {$table_name} WHERE lead_id=%d {$meta_filter}", $entry_id));

    //clears cache.
    $_gform_lead_meta = array();
}

