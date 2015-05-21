<?php
/*
Plugin Name: Share a Draft
Plugin URI: http://wordpress.org/extend/plugins/shareadraft/
Description: Let your friends preview one of your drafts, without giving them permissions to edit posts in your blog.
Author: Nikolay Bachiyski
Version: 1.4
Author URI: http://nikolay.bg/
Text Domain: shareadraft
Generated At: www.wp-fun.co.uk;
*/ 

if (!class_exists('ShareADraft')):
class ShareADraft	{
	var $admin_options_name = "ShareADraft_options";

	function __construct(){
		add_action('init', array($this, 'init'));
	}

	function init() {
		global $current_user;
		add_action('admin_menu', array($this, 'add_admin_pages'));
		add_filter('the_posts', array($this, 'the_posts_intercept'));
		add_filter('posts_results', array($this, 'posts_results_intercept'));

		$this->admin_options = $this->get_admin_options();
		$this->admin_options = $this->clear_expired($this->admin_options);
		$this->user_options = ($current_user->id > 0 && isset($this->admin_options[$current_user->id]))? $this->admin_options[$current_user->id] : array();

		$this->save_admin_options();
		load_plugin_textdomain('shareadraft', PLUGINDIR . '/shareadraft/languages');

		if (isset($_GET['page']) && $_GET['page'] == plugin_basename(__FILE__))
			$this->admin_page_init();
	}

	function admin_page_init() {
		wp_enqueue_script('jquery');
		add_action('admin_head', array($this, 'print_admin_css'));
		add_action('admin_head', array($this, 'print_admin_js'));
	}

	function get_admin_options() {
		$saved_options = get_option($this->admin_options_name);
		return is_array($saved_options)? $saved_options : array();
	}

	function save_admin_options(){
		global $current_user;
		if ($current_user->id > 0) {
			$this->admin_options[$current_user->id] = $this->user_options;
		}
		update_option($this->admin_options_name, $this->admin_options);
	}

	function clear_expired($all_options) {
		$all = array();
		foreach($all_options as $user_id => $options) {
			$shared = array();
			if (!isset($options['shared']) || !is_array($options['shared'])) {
				continue;
			}
			foreach($options['shared'] as $share) {
				if ($share['expires'] < time()) {
					continue;
				}
				$shared[] = $share;
			}
			$options['shared'] = $shared;
			$all[$user_id] = $options;
		}
		return $all;
	}

	function add_admin_pages(){
		add_submenu_page("edit.php", __('Share a Draft', 'shareadraft'), __('Share a Draft', 'shareadraft'),
			'edit_posts', __FILE__, array($this, 'output_existing_menu_sub_admin_page'));
	}

	function calculate_seconds($params) {
		$exp = 60;
		$multiply = 60;
		if (isset($params['expires']) && ($e = intval($params['expires']))) {
			$exp = $e;
		}
		$mults = array('s' => 1, 'm' => 60, 'h' => 3600, 'd' => 24*3600);
		if (isset($params['measure']) && isset($mults[$params['measure']])) {
			$multiply = $mults[$params['measure']];
		}
		return $exp * $multiply;
	}

	function process_post_options($params) {
		global $current_user;
		if (isset($params['post_id'])) {
			$p = get_post($params['post_id']);
			if (!$p) {
				return __('There is no such post!', 'shareadraft');
			}
			if ('publish' == get_post_status($p)) {
				return __('The post is published!', 'shareadraft');
			}
			$this->user_options['shared'][] = array('id' => $p->ID,
				'expires' => time() + $this->calculate_seconds($params),
				'key' => uniqid('baba'.$p->ID.'_'));
			$this->save_admin_options();
		}	
	}

	function process_delete($params) {
		if (!isset($params['key']) ||
			!isset($this->user_options['shared']) ||
		!is_array($this->user_options['shared'])) {
			return '';
		}
		$shared = array();
		foreach($this->user_options['shared'] as $share) {
			if ($share['key'] == $params['key']) {
				continue;
			}
			$shared[] = $share;
		}
		$this->user_options['shared'] = $shared;
		$this->save_admin_options();
	}

	function process_extend($params) {
		if (!isset($params['key']) ||
			!isset($this->user_options['shared']) ||
			!is_array($this->user_options['shared'])) {
			return '';
		}
		$shared = array();
		foreach($this->user_options['shared'] as $share) {
			if ($share['key'] == $params['key']) {
				$share['expires'] += $this->calculate_seconds($params);
			}
			$shared[] = $share;
		}
		$this->user_options['shared'] = $shared;
		$this->save_admin_options();
	}

	function get_drafts() {
		global $current_user;
		$my_drafts = get_users_drafts($current_user->id);
		$my_scheduled = $this->get_users_future($current_user->id);
		$pending = get_others_pending($current_user->id);
		$others_drafts = get_others_drafts($current_user->id);
		$drafts_struct = array(
			array(
				__('Your Drafts:', 'shareadraft'),
				count($my_drafts),
				$my_drafts,
			),
			array(
				__('Your Scheduled Posts:', 'shareadraft'),
				count($my_scheduled),
				$my_scheduled,
			),
			array(
				__('Pending Review:', 'shareadraft'),
				count($pending),
				$pending,
			),
			array(
				__('Others&#8217; Drafts:', 'shareadraft'),
				count($others_drafts),
				$others_drafts,
			),
		);
		return $drafts_struct; 
	}
	
	function get_users_future($user_id) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'future' AND post_author = %d ORDER BY post_modified DESC", $user_id);
		return $wpdb->get_results( $query );
	}

	function get_shared() {
		if (!isset($this->user_options['shared']) || !is_array($this->user_options['shared'])) {
			return array();
		}
		return $this->user_options['shared'];
	}

	function friendly_delta($s) {
		$m = (int)($s/60);
		$free_s = $s - $m*60;
		$h = (int)($s/3600);
		$free_m = (int)(($s - $h*3600)/60);
		$d = (int)($s/(24*3600));
		$free_h = (int)(($s - $d*(24*3600))/3600);
		if ($m < 1) {
			$res = array($s);
		} elseif ($h < 1) {
			$res = array($free_s, $m);
		} elseif ($d < 1) {
			$res = array($free_s, $free_m, $h);
		} else {
			$res = array($free_s, $free_m, $free_h, $d);
		}
		$names = array();
		if (isset($res[0]))
			$names[] = sprintf(__ngettext('%d second', '%d seconds', $res[0], 'shareadraft'), $res[0]);
		if (isset($res[1]))
			$names[] = sprintf(__ngettext('%d minute', '%d minutes', $res[1], 'shareadraft'), $res[1]);
		if (isset($res[2]))
			$names[] = sprintf(__ngettext('%d hour', '%d hours', $res[2], 'shareadraft'), $res[2]);
		if (isset($res[3]))
			$names[] = sprintf(__ngettext('%d day', '%d days', $res[3], 'shareadraft'), $res[3]);
		return implode(', ', array_reverse($names));
	}

	function output_existing_menu_sub_admin_page(){
		if (isset($_POST['shareadraft_submit'])) {
			$msg = $this->process_post_options($_POST);
		} elseif (isset($_POST['action']) && $_POST['action'] == 'extend') {
			$msg = $this->process_extend($_POST);
		} elseif (isset($_GET['action']) && $_GET['action'] == 'delete') {
			$msg = $this->process_delete($_GET);
		}
		$drafts_struct = $this->get_drafts();
?>
	<div class="wrap">
		<h2><?php _e('Share a Draft', 'shareadraft'); ?></h2>
<?php 	if ($msg):?>
		<div id="message" class="updated fade"><?php echo $msg; ?></div>
<?php 	endif;?>
		<h3><?php _e('Currently shared drafts', 'shareadraft'); ?></h3>
		<table class="widefat">
			<thead>
			<tr>
				<th><?php _e('ID', 'shareadraft'); ?></th>
				<th><?php _e('Title', 'shareadraft'); ?></th>
				<th><?php _e('Link', 'shareadraft'); ?></th>
				<th><?php _e('Expires after', 'shareadraft'); ?></th>
				<th colspan="2" class="actions"><?php _e('Actions', 'shareadraft'); ?></th>
			</tr>
			</thead>
			<tbody>
<?php
		$s = $this->get_shared();
		foreach($s as $share):
			$p = get_post($share['id']);
			$url = get_bloginfo('url') . '/?p=' . $p->ID . '&shareadraft='. $share['key'];
?>
			<tr>
				<td><?php echo $p->ID; ?></td>
				<td><?php echo $p->post_title; ?></td>
				<!-- TODO: make the draft link selecatble -->
				<td><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $url ); ?></a></td>
				<td><?php echo $this->friendly_delta($share['expires'] - time()); ?></td>
				<td class="actions">
					<a class="shareadraft-extend edit" id="shareadraft-extend-link-<?php echo $share['key']; ?>"
						href="javascript:shareadraft.toggle_extend('<?php echo $share['key']; ?>');">
							<?php _e('Extend', 'shareadraft'); ?>
					</a>
					<form class="shareadraft-extend" id="shareadraft-extend-form-<?php echo $share['key']; ?>"
						action="" method="post">
						<input type="hidden" name="action" value="extend" />
						<input type="hidden" name="key" value="<?php echo $share['key']; ?>" />
						<input type="submit" class="button" name="shareadraft_extend_submit"
							value="<?php echo attribute_escape(__('Extend', 'shareadraft')); ?>"/>
<?php _e('by', 'shareadraft');?>
<?php echo $this->tmpl_measure_select(); ?>
						<a class="shareadraft-extend-cancel"
							href="javascript:shareadraft.cancel_extend('<?php echo $share['key']; ?>');">
							<?php _e('Cancel', 'shareadraft'); ?>
						</a>
					</form>
				</td>
				<td class="actions">
					<a class="delete" href="edit.php?page=<?php echo plugin_basename(__FILE__); ?>&amp;action=delete&amp;key=<?php echo $share['key']; ?>"><?php _e('Delete', 'shareadraft'); ?></a>
				</td>
			</tr>
<?php
		endforeach;
		if (empty($s)):
?>
			<tr>
				<td colspan="5"><?php _e('No shared drafts!', 'shareadraft'); ?></td>
			</tr>
<?php
		endif;
?>
			</tbody>
		</table>
		<h3><?php _e('Share a Draft', 'shareadraft'); ?></h3>
		<form id="shareadraft-share" action="" method="post">
		<p>
			<select id="shareadraft-postid" name="post_id">
			<option value=""><?php _e('Choose a draft', 'shareadraft'); ?></option>
<?php
		foreach($drafts_struct as $draft_type):
			if ($draft_type[1]):
?>
			<option value="" disabled="disabled"></option>
			<option value="" disabled="disabled"><?php echo $draft_type[0]; ?></option>
<?php
				foreach($draft_type[2] as $draft):
					if (empty($draft->post_title)) continue;
?>
			<option value="<?php echo $draft->ID?>"><?php echo wp_specialchars($draft->post_title); ?></option>
<?php
				endforeach;
			endif;
		endforeach;
?>
			</select>
		</p>
		<p>
			<input type="submit" class="button" name="shareadraft_submit"
				value="<?php echo attribute_escape(__('Share it', 'shareadraft')); ?>" />
			<?php _e('for', 'shareadraft'); ?>
			<?php echo $this->tmpl_measure_select(); ?>.
		</p>
		</form>
		</div>
<?php
	}

	function can_view($post_id) {
		if (!isset($_GET['shareadraft']) || !is_array($this->admin_options)) {
			return false;
		}
		foreach($this->admin_options as $option) {
			if (!is_array($option) || !isset($option['shared'])) continue;
			$shares = $option['shared'];
			foreach($shares as $share) {
				if ($share['id'] == $post_id && $share['key'] == $_GET['shareadraft']) {
					return true;
				}
			}
		}
		return false;
	}

	function posts_results_intercept($posts) {
		if (1 != count($posts)) return $posts;
		$post = $posts[0];
		$status = get_post_status($post);
		if ('publish' != $status && $this->can_view($post->ID)) {
			$this->shared_post = $post;
		}
		return $posts;
	}

	function the_posts_intercept($posts){
		if (empty($posts) && !is_null($this->shared_post)) {
			return array($this->shared_post);
		} else {
			$this->shared_post = null;
			return $posts;
		}
	}

	function tmpl_measure_select() {
		$secs = __('seconds', 'shareadraft');
		$mins = __('minutes', 'shareadraft');
		$hours = __('hours', 'shareadraft');
		$days = __('days', 'shareadraft');
		return <<<SELECT
			<input name="expires" type="text" value="2" size="4"/>
			<select name="measure">
				<option value="s">$secs</option>
				<option value="m">$mins</option>
				<option value="h" selected="selected">$hours</option>
				<option value="d">$days</option>
			</select>
SELECT;
	}

	function print_admin_css() {
?>
	<style type="text/css">
		a.shareadraft-extend, a.shareadraft-extend-cancel { display: none; }
		form.shareadraft-extend { white-space: nowrap; }
		form.shareadraft-extend, form.shareadraft-extend input, form.shareadraft-extend select { font-size: 11px; }
		th.actions, td.actions { text-align: center; }
	</style>
<?php
	}

	function print_admin_js() {
?>
	<script type="text/javascript">
	//<![CDATA[
	(function($) {
		$(function() {
			$('form.shareadraft-extend').hide();
			$('a.shareadraft-extend').show();
			$('a.shareadraft-extend-cancel').show();
			$('a.shareadraft-extend-cancel').css('display', 'inline');
		});
		window.shareadraft = {
			toggle_extend: function(key) {
				$('#shareadraft-extend-form-'+key).show();
				$('#shareadraft-extend-link-'+key).hide();
				$('#shareadraft-extend-form-'+key+' input[name="expires"]').focus();
			},
			cancel_extend: function(key) {
				$('#shareadraft-extend-form-'+key).hide();
				$('#shareadraft-extend-link-'+key).show();
			}
		};
	})(jQuery);
	//]]>
	</script>
<?php
	}
}
endif;

if (class_exists('ShareADraft')) {
	$__share_a_draft = new ShareADraft();
}
