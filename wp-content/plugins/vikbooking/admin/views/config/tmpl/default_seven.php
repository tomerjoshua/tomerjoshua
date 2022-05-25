<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$dbo = JFactory::getDbo();
$app = JFactory::getApplication();
$vbo_app = VikBooking::getVboApplication();

VikBooking::getConditionalRulesInstance(true);
$tpl_names = VikBookingHelperConditionalRules::getTemplateFilesNames();
$tpl_contents = VikBookingHelperConditionalRules::getTemplateFilesContents();

$lim = $app->getUserStateFromRequest("com_vikbooking.limit", 'limit', $app->get('list_limit'), 'int');
$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');

// load lang vars for JS
JText::script('VBDELCONFIRM');
JText::script('VBOCONFIGEDITTMPLFILE');
JText::script('VBO_CONDTEXT_TAG_ADD_HELP');
JText::script('VBO_CONDTEXT_TAG_RM_HELP');
JText::script('VBO_CSS_EDITING_HELP');
JText::script('VBO_INSPECTOR_START');
JText::script('VBO_EDITTPL_FATALERROR');

$rows = array();
$navbut = "";

$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikbooking_condtexts` ORDER BY `#__vikbooking_condtexts`.`lastupd` DESC";
$dbo->setQuery($q, $lim0, $lim);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$rows = $dbo->loadAssocList();
	$dbo->setQuery('SELECT FOUND_ROWS();');
	jimport('joomla.html.pagination');
	$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
	$navbut = "<table align=\"center\"><tr><td>".$pageNav->getPagesLinks()."</td></tr></table>";
}

?>
<div class="vbo-config-maintab-top">
	<fieldset class="adminform vbo-config-fieldset-large">
		<div class="vbo-params-wrap">
			<legend class="adminlegend"><?php echo JText::translate('VBO_COND_TEXT_RULES'); ?></legend>
			<div class="vbo-params-container">
				<div class="vbo-param-container">
					<div class="vbo-param-setting">
						<a href="index.php?option=com_vikbooking&task=newcondtext" class="btn vbo-config-btn"><?php VikBookingIcons::e('plus-circle'); ?> <?php echo JText::translate('VBO_NEW_COND_TEXT'); ?></a>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-setting">
						<div class="table-responsive vbo-list-table-rounded">
							<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
								<thead>
									<tr>
										<th class="title left" width="200"><?php echo JText::translate( 'VBO_CONDTEXT_NAME' ); ?></th>
										<th class="title left" width="200"><?php echo JText::translate( 'VBO_CONDTEXT_TKN' ); ?></th>
										<th class="title center" width="100"><?php echo JText::translate( 'VBO_WIDGETS_LASTUPD' ); ?></th>
										<th class="title center" width="300"><?php echo JText::translate( 'VBO_TEMPLATE_FILES' ); ?></th>
										<th class="title center" width="100">&nbsp;</th>
									</tr>
								</thead>
							<?php
							$k = 0;
							$i = 0;
							for ($i = 0, $n = count($rows); $i < $n; $i++) {
								$row = $rows[$i];
								?>
								<tr class="row<?php echo $k; ?>">
									<td>
										<a href="index.php?option=com_vikbooking&amp;task=editcondtext&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
									</td>
									<td>
										<span class="vbo-condtext-token-full"><?php echo $row['token']; ?></span>
									</td>
									<td class="center"><?php echo $row['lastupd']; ?></td>
									<td class="center">
									<?php
									foreach ($tpl_names as $file => $name) {
										$tag_used = VikBookingHelperConditionalRules::isTagInContent($row['token'], $tpl_contents[$file]);
										$btn_icon = $tag_used ? VikBookingIcons::i('check-circle') : VikBookingIcons::i('plus');
										$btn_classes = array(
											'btn',
											'vbo-condtext-tmpl-status',
										);
										if ($tag_used) {
											array_push($btn_classes, 'btn-success');
											array_push($btn_classes, 'vbo-condtext-intmpl');
										} else {
											array_push($btn_classes, 'btn-secondary');
											array_push($btn_classes, 'vbo-condtext-notintmpl');
										}
										?>
										<button type="button" class="<?php echo implode(' ', $btn_classes); ?>" data-inspectfile="<?php echo $file; ?>" data-specialtag="<?php echo $row['token']; ?>"><i class="<?php echo $btn_icon; ?>"></i> <?php echo $name; ?></button>
										<?php
									}
									?>
									</td>
									<td class="center">
										<a href="index.php?option=com_vikbooking&amp;task=removecondtext&amp;cid[]=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm(Joomla.JText._('VBDELCONFIRM'));"><?php VikBookingIcons::e('trash'); ?> <?php echo JText::translate('VBMAINCRONDEL'); ?></a>
									</td>
								</tr>	
								<?php
								$k = 1 - $k;
							}
							?>
							</table>
							<?php echo $navbut; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset class="adminform vbo-config-fieldset-small">
		<div class="vbo-params-wrap">
			<legend class="adminlegend"><?php echo JText::translate('VBOCONFIGEDITTMPLFILE'); ?></legend>
			<div class="vbo-params-container">
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::translate('VBOCONFIGEMAILTEMPLATE'); ?></div>
					<div class="vbo-param-setting">
						<button type="button" class="btn vbo-inspector-btn" title="<?php echo addslashes(JText::translate('VBO_INSPECTOR_START')); ?>" data-inspectfile="email_tmpl.php"><?php VikBookingIcons::e('paint-brush'); ?></button>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::translate('VBOCONFIGINVOICETEMPLATE'); ?></div>
					<div class="vbo-param-setting">
						<button type="button" class="btn vbo-inspector-btn" title="<?php echo addslashes(JText::translate('VBO_INSPECTOR_START')); ?>" data-inspectfile="invoice_tmpl.php"><?php VikBookingIcons::e('paint-brush'); ?></button>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-label"><?php echo JText::translate('VBOCONFIGCHECKINTEMPLATE'); ?></div>
					<div class="vbo-param-setting">
						<button type="button" class="btn vbo-inspector-btn" title="<?php echo addslashes(JText::translate('VBO_INSPECTOR_START')); ?>" data-inspectfile="checkin_tmpl.php"><?php VikBookingIcons::e('paint-brush'); ?></button>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<div id="vbo-inspector-outer" class="vbo-config-maintab-bottom" style="display: none;">
	<fieldset class="adminform">
		<div class="vbo-params-wrap">
			<legend class="adminlegend"><span id="vbo-inspector-title"></span></legend>
			<div class="vbo-params-container">
				<div class="vbo-param-container">
					<div class="vbo-param-setting">
						<div class="vbo-inspector-cmds">
							<button id="vbo-inspector-save" type="button" class="btn btn-success" disabled><?php VikBookingIcons::e('save'); ?> <?php echo JText::translate('VBSAVE'); ?></button>
							<button id="vbo-inspector-cancel" type="button" class="btn btn-secondary"><?php VikBookingIcons::e('times'); ?> <?php echo JText::translate('VBANNULLA'); ?></button>
						</div>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-setting">
						<p id="vbo-inspector-help" class="info notice-noicon"></p>
					</div>
				</div>
				<div class="vbo-param-container vbo-inspector-css-param" style="display: none;">
					<div class="vbo-param-label"><?php echo JText::translate('VBO_INSP_HTML_TAG'); ?></div>
					<div class="vbo-param-setting">
						<span id="vbo-inspector-css-tag" class="vbo-inspector-css-tag"></span>
					</div>
				</div>
				<div class="vbo-param-container vbo-inspector-css-param" style="display: none;">
					<div class="vbo-param-label"><?php echo JText::translate('VBO_INSP_CSS_FONTCOLOR'); ?></div>
					<div class="vbo-param-setting">
						<span class="vbo-inspector-colorpicker-wrap">
							<span id="vbo-inspector-css-color" class="vbo-inspector-colorpicker vbo-inspector-colorpicker-trig"><?php VikBookingIcons::e('palette'); ?></span>
						</span>
					</div>
				</div>
				<div class="vbo-param-container vbo-inspector-css-param" style="display: none;">
					<div class="vbo-param-label"><?php echo JText::translate('VBO_INSP_CSS_BACKGCOLOR'); ?></div>
					<div class="vbo-param-setting">
						<span class="vbo-inspector-colorpicker-wrap">
							<span id="vbo-inspector-css-backgcolor" class="vbo-inspector-colorpicker vbo-inspector-colorpicker-trig"><?php VikBookingIcons::e('palette'); ?></span>
						</span>
					</div>
				</div>
				<div class="vbo-param-container vbo-inspector-css-param" style="display: none;">
					<div class="vbo-param-label"><?php echo JText::translate('VBO_INSP_CSS_BORDER'); ?></div>
					<div class="vbo-param-setting vbo-toggle-small">
						<?php echo $vbo_app->printYesNoButtons('vbo-inspector-css-border', JText::translate('VBYES'), JText::translate('VBNO'), 0, 1, 0, 'vboToggleCSSBorderParams();'); ?>
					</div>
				</div>
				<div class="vbo-param-container vbo-inspector-css-border-param" style="display: none;">
					<div class="vbo-param-label"><?php echo JText::translate('VBO_INSP_CSS_BORDERWIDTH'); ?> (px)</div>
					<div class="vbo-param-setting">
						<input type="number" min="0" id="vbo-inspector-css-borderwidth" value="0" />
					</div>
				</div>
				<div class="vbo-param-container vbo-inspector-css-border-param" style="display: none;">
					<div class="vbo-param-label"><?php echo JText::translate('VBO_INSP_CSS_BORDERCOLOR'); ?></div>
					<div class="vbo-param-setting">
						<span class="vbo-inspector-colorpicker-wrap">
							<span id="vbo-inspector-css-bordercolor" class="vbo-inspector-colorpicker vbo-inspector-colorpicker-trig"><?php VikBookingIcons::e('palette'); ?></span>
						</span>
					</div>
				</div>
				<div class="vbo-param-container">
					<div class="vbo-param-setting">
						<div class="vbo-inspector-wrap" data-inspectfile=""></div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<?php
foreach ($tpl_contents as $file => $content) {
	?>
<div class="vbo-tplcontent-inspector-ghost" style="display: none;" data-inspectfile="<?php echo $file; ?>">
	<?php echo $content; ?>
</div>
	<?php
}
?>

<a class="vbo-reload-plchld-link" href="index.php?option=com_vikbooking&task=config" style="display: none;">&nbsp;</a>

<script type="text/javascript">
	/**
	 * Define global scope vars.
	 */
	var tpl_names = <?php echo json_encode($tpl_names); ?>;
	var current_tag = null,
		current_file = null,
		current_target = null,
		css_editing = false,
		css_custom_classes = [];

	/**
	 * Generates a unique class for the target in the CSS inspector.
	 */
	function vboTargetUniqueClassName() {
		var now = new Date;
		return 'vbo-inspector-custom-' + now.getTime();
	}

	/**
	 * Adds the temporary class of the current target to the pool.
	 * Should be called when target's CSS properties have been modified.
	 * All classes in the pool may be processed via PHP for update.
	 */
	function vboCSSTargetChanged() {
		if (current_target === null) {
			return false;
		}
		var tmpclass = current_target.data('tmpclass');
		if (!tmpclass) {
			return false;
		}
		if (css_custom_classes.indexOf(tmpclass) < 0) {
			// push custom/temporary CSS rule
			css_custom_classes.push(tmpclass);
			// enable save button
			jQuery('#vbo-inspector-save').prop('disabled', false);
		}
	}

	/**
	 * Unbinds the hover events for the inspector.
	 */
	function vboUnregisterInspector() {
		jQuery('.vbo-inspector-wrap').find('*').not('center, table, thead, tbody, tr').unbind('mouseenter mouseleave');
	}

	/**
	 * Register the hover events for the inspector.
	 */
	function vboRegisterInspector(unregister) {
		if (unregister === true) {
			vboUnregisterInspector();
		}

		jQuery('.vbo-inspector-wrap').find('*').not('center, table, thead, tbody, tr').hover(function() {
			jQuery('.vbo-inspector-hover').removeClass('vbo-inspector-hover');
			jQuery(this).addClass('vbo-inspector-hover');
		}, function() {
			if (jQuery(this).parent().length && jQuery(this).parent().is(':hover')) {
				jQuery(this).parent().addClass('vbo-inspector-hover');
			}
			jQuery(this).removeClass('vbo-inspector-hover');
		});

		jQuery('.vbo-inspector-wrap').mouseleave(function() {
			jQuery('.vbo-inspector-hover').removeClass('vbo-inspector-hover');
		});
	}

	/**
	 * Enables the CSS editing mode.
	 */
	function vboEnableCSSEditing() {
		css_editing = true;
	}

	/**
	 * Disables the CSS editing mode.
	 */
	function vboDisableCSSEditing() {
		css_editing = false;
		// hide CSS params
		jQuery('.vbo-inspector-css-param, .vbo-inspector-css-border-param').hide();
	}

	/**
	 * Starts the CSS inspector. Can be called also by other tabs in this page.
	 */
	function vboStartCSSInspector(file) {
		// when a specific file is requested, simulate the cancel action
		if (file && jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').length) {
			// hide current CSS params
			jQuery('.vbo-inspector-css-param, .vbo-inspector-css-border-param').hide();
			// unset current target
			current_target = null;
			// make the list of targets with custom CSS empty
			css_custom_classes = [];
			// disable save button
			jQuery('#vbo-inspector-save').prop('disabled', true);
			// sanitize template source code from any script tag in case of malformed markup
			jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').find('script').remove();
			// grab requested content and move it to the inspector wrapper
			jQuery('.vbo-inspector-wrap').html(jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').html()).attr('data-inspectfile', file);
			// register hovering event
			vboRegisterInspector(true);
		}
		if (!file && current_file !== null) {
			// triggers after adding and saving a conditional text tag
			file = current_file;
		}
		if (!tpl_names.hasOwnProperty(file)) {
			console.error('file not found', file, tpl_names);
			return false;
		}
		// turn off adding tag mode
		current_tag = null;
		// define global file editing
		current_file = file;
		// enable flag for CSS editing
		vboEnableCSSEditing();
		// update title
		jQuery('#vbo-inspector-title').text(tpl_names[file] + ' - ' + Joomla.JText._('VBO_INSPECTOR_START'));
		// update helper
		jQuery('#vbo-inspector-help').html('<?php VikBookingIcons::e('paint-brush'); ?> ' + Joomla.JText._('VBO_CSS_EDITING_HELP')).show();
		// display inspector outer
		jQuery('#vbo-inspector-outer').show();
		if (current_target !== null) {
			// render CSS properties
			vboRenderCSSInspector();
		} else {
			// animate scroll to the outer position
			jQuery('html,body').animate({scrollTop: jQuery('#vbo-inspector-outer').offset().top - 40}, {duration: 400});
		}
	}

	/**
	 * Displays the CSS properties of the selected target.
	 * Creates a new class identifier for the current target.
	 */
	function vboRenderCSSInspector() {
		if (current_target === null || !css_editing) {
			console.error('CSS editing not available', current_target, css_editing);
			return false;
		}
		// add the unique class to the target element
		var unique_class_id = vboTargetUniqueClassName();
		current_target.removeClass('vbo-inspector-hover').addClass(unique_class_id).data('tmpclass', unique_class_id);
		// set current tag name
		jQuery('#vbo-inspector-css-tag').text(current_target.prop('tagName'));
		// set current font color
		jQuery('#vbo-inspector-css-color').css('backgroundColor', current_target.css('color'));
		// set current background color
		jQuery('#vbo-inspector-css-backgcolor').css('backgroundColor', current_target.css('backgroundColor'));
		// check if element has borders and set values
		var elborder = current_target.css('border-left-width').match(/^([0-9.]+)/);
		if (elborder && elborder[1] > 0) {
			// has got a border
			jQuery('input[name="vbo-inspector-css-border"]').prop('checked', true);
			jQuery('.vbo-inspector-css-border-param').show();
			jQuery('#vbo-inspector-css-borderwidth').val(elborder[1]);
			jQuery('#vbo-inspector-css-bordercolor').css('backgroundColor', current_target.css('border-left-color'));
		} else {
			// has got no border
			jQuery('input[name="vbo-inspector-css-border"]').prop('checked', false);
			jQuery('.vbo-inspector-css-border-param').hide();
			jQuery('#vbo-inspector-css-borderwidth').val('0');
			// unset background completely, not just the bgcolor
			jQuery('#vbo-inspector-css-bordercolor').css('background', '');
		}
		// display CSS params
		jQuery('.vbo-inspector-css-param').show();
		// animate scroll to the outer position
		jQuery('html,body').animate({scrollTop: jQuery('#vbo-inspector-outer').offset().top - 40}, {duration: 400});
	}

	/**
	 * Toggles the CSS params for the border
	 */
	function vboToggleCSSBorderParams() {
		if (jQuery('input[name="vbo-inspector-css-border"]').is(':checked')) {
			jQuery('.vbo-inspector-css-border-param').show();
			if (current_target !== null) {
				// restore border, if any
				var borderwidth = jQuery('#vbo-inspector-css-borderwidth').val();
				if (borderwidth.length && !isNaN(borderwidth) && borderwidth > 0) {
					current_target.css('border', borderwidth + 'px solid ' + vboRgb2Hex(jQuery('#vbo-inspector-css-bordercolor').css('backgroundColor')));
				}
			}
		} else {
			jQuery('.vbo-inspector-css-border-param').hide();
			if (current_target !== null) {
				// unset border completely
				current_target.css('border', '');
			}
		}
	}

	/**
	 * AJAX request to remove a special tag from a template file.
	 */
	function vboRemoveCondTextTagFromTpl(tag, file) {
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: {
				option: "com_vikbooking",
				task: "condtext_update_tmpl",
				tmpl: "component",
				tagaction: 'remove',
				tag: tag,
				file: file
			}
		}).done(function(response) {
			try {
				var obj_res = JSON.parse(response);
				if (!obj_res.hasOwnProperty('newhtml')) {
					console.error('Unexpected JSON response', obj_res);
					return false;
				}
				// update template file HTML code
				jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').html(obj_res['newhtml']);
				// update button classes and icon
				var btn_triggering = jQuery('.vbo-condtext-tmpl-status[data-inspectfile="' + file + '"][data-specialtag="' + tag + '"]');
				btn_triggering.removeClass('btn-success vbo-condtext-intmpl').addClass('btn-secondary vbo-condtext-notintmpl');
				btn_triggering.find('i').removeClass().addClass('<?php echo VikBookingIcons::i('plus'); ?>');
			} catch(err) {
				console.error('could not parse JSON response', err, response);
				alert('Request failed');
			}
		}).fail(function() {
			alert('Request failed');
			console.error('Request failed');
		});
	}

	/**
	 * Removes (undo) a special tag from the inspector.
	 */
	function vboRemoveTagFromInspector() {
		if (current_tag === null) {
			return false;
		}
		var escape_tag = current_tag.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&');
		// replace all occurrences of escaped tag
		var empty_html = jQuery('.vbo-inspector-wrap').html();
		empty_html = empty_html.replace(new RegExp(escape_tag, 'g'), '');
		// restore correct HTML
		jQuery('.vbo-inspector-wrap').html(empty_html);
	}

	/**
	 * When manipulating the source code of the template files, errors may occur.
	 * This function makes a new AJAX request to restore the file from the backup.
	 */
	function vboRestoreBackupFile() {
		if (current_file === null) {
			console.error('file cannot be null, unable to restore file');
			return false;
		}
		// make the request
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: {
				option: "com_vikbooking",
				task: "condtext_update_tmpl",
				tmpl: "component",
				tagaction: 'restore',
				file: current_file
			}
		}).done(function(response) {
			var reload_href = jQuery('.vbo-reload-plchld-link').attr('href');
			try {
				var obj_res = JSON.parse(response);
				if (!obj_res.hasOwnProperty('newhtml')) {
					console.error('Unexpected JSON response while restoring', obj_res, response);
					return false;
				}
				location.href = reload_href;
				return;
			} catch(err) {
				console.error('could not parse JSON response when restoring', err, response);
				// display error message
				alert('Restoring failed. The template file ' + current_file + ' may be corrupted');
				location.href = reload_href;
				return;
			}
		}).fail(function() {
			alert('Restoring request failed');
			console.error('Restoring request failed');
		});
	}

	/**
	 * Declare the functions for the document ready event.
	 */
	jQuery(document).ready(function() {

		/**
		 * Register border width number input change event.
		 */
		jQuery('#vbo-inspector-css-borderwidth').change(function() {
			if (current_target === null) {
				return;
			}
			var borderwidth = jQuery(this).val();
			if (borderwidth.length && !isNaN(borderwidth) && borderwidth > 0) {
				// use short-hand syntax to update the border
				current_target.css('border', borderwidth + 'px solid ' + vboRgb2Hex(jQuery('#vbo-inspector-css-bordercolor').css('backgroundColor')));
			} else {
				// unset the border completely
				current_target.css('border', '');
			}
			// trigger target style modification
			vboCSSTargetChanged();
		});

		/**
		 * Register color-picker for CSS editing parameters.
		 */
		jQuery('.vbo-inspector-colorpicker-trig').ColorPicker({
			color: '#ffffff',
			onShow: function (colpkr, el) {
				if (current_target === null) {
					return false;
				}
				var cur_color = jQuery(el).css('backgroundColor');
				jQuery(el).ColorPickerSetColor(vboRgb2Hex(cur_color));
				jQuery(colpkr).show();
				return false;
			},
			onChange: function (hsb, hex, rgb, el) {
				var element = jQuery(el);
				var elid = element.attr('id');
				element.css('backgroundColor', '#'+hex);
				if (current_target !== null) {
					var css_prop = 'backgroundColor';
					if (elid == 'vbo-inspector-css-color') {
						css_prop = 'color';
					} else if (elid == 'vbo-inspector-css-bordercolor') {
						css_prop = 'borderColor';
					}
					current_target.css(css_prop, '#'+hex);
				}
				// trigger target style modification
				vboCSSTargetChanged();
			},
			onSubmit: function(hsb, hex, rgb, el) {
				var element = jQuery(el);
				var elid = element.attr('id');
				element.css('backgroundColor', '#'+hex);
				if (current_target !== null) {
					var css_prop = 'backgroundColor';
					if (elid == 'vbo-inspector-css-color') {
						css_prop = 'color';
					} else if (elid == 'vbo-inspector-css-bordercolor') {
						css_prop = 'borderColor';
					}
					current_target.css(css_prop, '#'+hex);
				}
				element.ColorPickerHide();
				// trigger target style modification
				vboCSSTargetChanged();
			}
		});

		/**
		 * Listens to the buttons to start the CSS editing mode. May be used on different tabs.
		 */
		jQuery('.vbo-inspector-btn').click(function() {
			var file = jQuery(this).attr('data-inspectfile');
			if (!file || !tpl_names.hasOwnProperty(file)) {
				alert('Template not found');
				console.error('Template not found ' + file, tpl_names);
				return false;
			}
			// make sure the proper configuration tab is active
			var proper_tab_cont = jQuery('.vbo-inspector-wrap').closest('dd.tabs');
			if (!proper_tab_cont.is(':visible')) {
				var tab_id = proper_tab_cont.attr('id').replace('pt', '');
				if (jQuery('dt.tabs[data-ptid="' + tab_id + '"]').length) {
					// simulate click on proper tab, as CSS editing can be started also from another tab
					jQuery('dt.tabs[data-ptid="' + tab_id + '"]').trigger('click');
				}
			}
			// start CSS inspector
			vboStartCSSInspector(file);
		});

		/**
		 * Listens to the click on the buttons to add or remove special tags.
		 */
		jQuery('.vbo-condtext-tmpl-status').click(function() {
			// always hide inspector outer
			jQuery('#vbo-inspector-outer').hide();
			// always make the helper text empty
			jQuery('#vbo-inspector-help').html('');
			// always disable the save button
			jQuery('#vbo-inspector-save').prop('disabled', true);
			// always disable CSS editing
			vboDisableCSSEditing();
			// find file name and tag
			var file = jQuery(this).attr('data-inspectfile');
			if (!file || !tpl_names.hasOwnProperty(file)) {
				alert('Template not found');
				console.error('Template not found ' + file, tpl_names);
				return false;
			}
			// update global current tag and file selected
			var tag = jQuery(this).attr('data-specialtag');
			current_tag = tag;
			current_file = file;
			// check if tag is already in the template file
			if (jQuery(this).hasClass('vbo-condtext-intmpl')) {
				// ask to remove it
				var rmtxt = Joomla.JText._('VBO_CONDTEXT_TAG_RM_HELP');
				if (confirm(rmtxt.replace('%s', tpl_names[file]))) {
					vboRemoveCondTextTagFromTpl(current_tag, file);
				} else {
					current_tag = null;
					current_file = null;
				}
				return false;
			}
			// update title
			jQuery('#vbo-inspector-title').text(tpl_names[file] + ' - ' + Joomla.JText._('VBOCONFIGEDITTMPLFILE'));
			// update helper
			var helper_txt = Joomla.JText._('VBO_CONDTEXT_TAG_ADD_HELP');
			jQuery('#vbo-inspector-help').html('<?php VikBookingIcons::e('magic'); ?> ' + helper_txt.replace('%s', current_tag)).show();
			// sanitize template source code from any script tag in case of malformed markup
			jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').find('script').remove();
			// grab requested content and move it to the inspector wrapper
			jQuery('.vbo-inspector-wrap').html(jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + file + '"]').html()).attr('data-inspectfile', file);
			// display inspector outer
			jQuery('#vbo-inspector-outer').show();
			// animate scroll to the outer position
			jQuery('html,body').animate({scrollTop: jQuery('#vbo-inspector-outer').offset().top - 40}, {duration: 400});
			// register hovering event
			vboRegisterInspector(true);
		});

		/**
		 * Listens to the click on the elements of the HTML inspector.
		 * It can append a special tag, remove one before, or start the CSS editing.
		 */
		jQuery('.vbo-inspector-wrap').click(function(e) {
			if (current_file === null) {
				// file cannot be null
				return false;
			}
			// update current target
			var target = jQuery(e.target);
			current_target = target;
			// insert tag, if requested
			if (current_tag != null) {
				// check if the tag was already added
				var removed = false;
				if (jQuery('.vbo-inspector-wrap').html().indexOf(current_tag) >= 0) {
					// remove current tag before appending it to the new position
					vboRemoveTagFromInspector();
					removed = true;
				}
				target.append(current_tag);
				// enable the save button
				jQuery('#vbo-inspector-save').prop('disabled', false);
				if (removed) {
					// inspector hovering needs to be re-registered after removing the tag
					vboRegisterInspector(true);
				}
				return;
			}
			// if we reach this point, start CSS inspector
			vboStartCSSInspector(null);
		});

		/**
		 * Tries to undo the last action. Behaves differently depending on the active mode.
		 */
		jQuery('#vbo-inspector-cancel').click(function() {
			if (current_file === null) {
				// file cannot be null, hide inspector outer no matter what
				jQuery('#vbo-inspector-outer').hide();
				return false;
			}
			if (current_tag != null) {
				// unset all occurrences of the current tag (if any)
				var empty_html = jQuery('.vbo-inspector-wrap').html();
				if (empty_html.length && empty_html.indexOf(current_tag) >= 0) {
					// remove tag from inspector
					vboRemoveTagFromInspector();
					// inspector hovering needs to be re-registered after editing the content
					vboRegisterInspector(true);
				} else {
					// hide inspector outer, as the tag was not added yet
					jQuery('#vbo-inspector-outer').hide();
				}
			} else {
				if (css_editing && current_target !== null) {
					// hide current CSS params
					jQuery('.vbo-inspector-css-param, .vbo-inspector-css-border-param').hide();
					// unset current target
					current_target = null;
					// make the list of targets with custom CSS empty
					css_custom_classes = [];
					// disable save button
					jQuery('#vbo-inspector-save').prop('disabled', true);
					// sanitize template source code from any script tag in case of malformed markup
					jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + current_file + '"]').find('script').remove();
					// cancelling during CSS editing means restoring the original source code
					jQuery('.vbo-inspector-wrap').html(jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + current_file + '"]').html()).attr('data-inspectfile', current_file);
					// register hovering event is necessary
					vboRegisterInspector(true);
				} else {
					// hide inspector outer, tag not yet added or already added and saved
					jQuery('#vbo-inspector-outer').hide();
				}
			}
		});

		/**
		 * Saves the last action made through the inspector.
		 */
		jQuery('#vbo-inspector-save').click(function() {
			if (current_file === null) {
				console.error('file cannot be null');
				return false;
			}
			var savebtn = jQuery(this);
			// show loading
			savebtn.find('i').removeClass().addClass('<?php echo VikBookingIcons::i('refresh', 'fa-spin fa-fw'); ?>');
			// detect action type
			var action_type = 'add';
			if (css_editing && css_custom_classes.length) {
				action_type = 'styles';
			}
			// make the request
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "index.php",
				data: {
					option: "com_vikbooking",
					task: "condtext_update_tmpl",
					tmpl: "component",
					tagaction: action_type,
					tag: current_tag,
					file: current_file,
					newcontent: jQuery('.vbo-inspector-wrap').html(),
					custom_classes: css_custom_classes
				}
			}).done(function(response) {
				try {
					var obj_res = JSON.parse(response);
					if (!obj_res.hasOwnProperty('newhtml')) {
						console.error('Unexpected JSON response', obj_res);
						return false;
					}
					// stop loading
					savebtn.find('i').removeClass().addClass('<?php echo VikBookingIcons::i('check-circle'); ?>');
					// disable another saving
					savebtn.prop('disabled', true);
					if (current_tag !== null) {
						// update button classes and icon
						var btn_triggering = jQuery('.vbo-condtext-tmpl-status[data-inspectfile="' + current_file + '"][data-specialtag="' + current_tag + '"]');
						btn_triggering.removeClass('btn-secondary vbo-condtext-notintmpl').addClass('btn-success vbo-condtext-intmpl');
						btn_triggering.find('i').removeClass().addClass('<?php echo VikBookingIcons::i('check-circle'); ?>');
						// hide helper
						jQuery('#vbo-inspector-help').hide().html('');
						// unset tag editing mode
						current_tag = null;
					}
					if (css_editing && css_custom_classes.length) {
						// empty the list of custom CSS classes as they were all saved
						css_custom_classes = [];
					}
					// update template file HTML code
					jQuery('.vbo-tplcontent-inspector-ghost[data-inspectfile="' + current_file + '"]').html(obj_res['newhtml']);
				} catch(err) {
					console.error('could not parse JSON response after saving', err, response);
					// display error message
					alert(Joomla.JText._('VBO_EDITTPL_FATALERROR'));
					// restore backup of the file source code
					vboRestoreBackupFile();
				}
			}).fail(function() {
				console.error('Request failed completely');
				// display error message
				alert(Joomla.JText._('VBO_EDITTPL_FATALERROR'));
				// restore backup of the file source code
				vboRestoreBackupFile();
			});
		});

	});
</script>
