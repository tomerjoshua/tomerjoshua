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

$rows = $this->rows;
$allrooms = $this->allrooms;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$orderby = $this->orderby;
$ordersort = $this->ordersort;

// list of IDs for which VCM should monitor the guest messages in the chat
$vcm_bids = array();

$dbo = JFactory::getDbo();
$app = JFactory::getApplication();

$vbo_app = VikBooking::getVboApplication();
$vbo_app->loadDatePicker();

$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);
$juidf = $nowdf == "%d/%m/%Y" ? 'dd/mm/yy' : ($nowdf == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');

$cid = VikRequest::getVar('cid', array());
$pcust_id = $app->getUserStateFromRequest("vbo.orders.cust_id", 'cust_id', 0, 'int');
$pconfirmnumber = VikRequest::getString('confirmnumber', '', 'request');
//Color tags
$colortags = VikBooking::loadBookingsColorTags();
$bctags_tip = '';
if (count($colortags) > 0) {
	$bctags_tip = '<div class=\"vbo-blist-tip-bctag-subtip-inner\">';
	foreach ($colortags as $ctagk => $ctagv) {
		$bctags_tip .= '<div class=\"vbo-blist-tip-bctag-subtip-circle hasTooltip\" data-ctagkey=\"'.$ctagk.'\" data-ctagcolor=\"'.$ctagv['color'].'\" title=\"'.addslashes(JText::translate($ctagv['name'])).'\"><div class=\"vbo-blist-tip-bctag-subtip-circlecont\" style=\"background-color: '.$ctagv['color'].';\"></div></div>';
	}
	$bctags_tip .= '</div>';
}
//

if (empty($rows)) {
	$rows = array();
	?>
	<p class="warn"><?php echo JText::translate('VBNOORDERSFOUND'); ?></p>
	<?php
	/**
	 * In case of no bookings found, we check whether bookings can be imported from third party plugins.
	 * 
	 * @wponly 	we do this only on WP.
	 * 
	 * @since 	1.3.5
	 */
	$supported_plugins = VikBooking::canImportBookingsFromThirdPartyPlugins();
	if ($supported_plugins !== false) {
		$tpp_key = '';
		foreach ($supported_plugins as $spkey => $spn) {
			$tpp_key = $spkey;
			break;
		}
		?>
	<div class="notice is-dismissible notice-info">
		<div class="vbo-import-wrap">
			<p>
				<span><?php echo JText::sprintf('VBO_IMPBFROM_INTO_VBO', (isset($supported_plugins[$tpp_key]) ? $supported_plugins[$tpp_key] : '')); ?></span>
				<a class="btn btn-success" href="admin.php?option=com_vikbooking&view=importbftpp"><?php echo JText::translate('VBO_IMPBFTPP_DOIMPORT_SHORT'); ?></a>
			</p>
		</div>
	</div>
		<?php
	}
}

//1.6 filter by channel
$all_channels = array();
$q = "SELECT `channel` FROM `#__vikbooking_orders` WHERE `channel` IS NOT NULL GROUP BY `channel`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ord_channels = $dbo->loadAssocList();
	foreach ($ord_channels as $o_channel) {
		$channel_parts = explode('_', $o_channel['channel']);
		$channel_name = count($channel_parts) > 1 ? trim($channel_parts[1]) : trim($channel_parts[0]);
		if (in_array($channel_name, $all_channels)) {
			continue;
		}
		$all_channels[] = $channel_name;
	}
}
//Prepare modal
echo $vbo_app->getJmodalScript();
echo $vbo_app->getJmodalHtml('vbo-export-csv', JText::translate('VBCSVEXPORT'), '', 'width: 80%; height: 60%; margin-left: -40%; top: 20% !important;');
echo $vbo_app->getJmodalHtml('vbo-export-ics', JText::translate('VBICSEXPORT'), '', 'width: 80%; height: 60%; margin-left: -40%; top: 20% !important;');
//end Prepare modal
$filters_set = false;
?>
<script type="text/javascript">
function vboClearFilters() {
	jQuery('#filter-bar, #vbo-search-tools-cont').find('input, select').val('');
	jQuery('#adminForm').append('<input type=\'hidden\' name=\'limitstart\' value=\'0\' />');
	document.getElementById('cust_id').value = '';
	document.adminForm.submit();
}
</script>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm" class="vbo-allbookings-fm">

<div id="filter-bar" class="btn-toolbar vbo-btn-toolbar" style="width: 100%; display: inline-block;">
	<div class="btn-group pull-right">
		<a href="javascript: void(0);" onclick="vboOpenJModal('vbo-export-csv', 'index.php?option=com_vikbooking&task=csvexportprepare&tmpl=component');" class="vbcsvexport"><?php VikBookingIcons::e('table'); ?> <span><?php echo JText::translate('VBCSVEXPORT'); ?></span></a>
		<a href="javascript: void(0);" onclick="vboOpenJModal('vbo-export-ics', 'index.php?option=com_vikbooking&task=icsexportprepare&tmpl=component');" class="vbicsexport"><?php VikBookingIcons::e('calendar'); ?> <span><?php echo JText::translate('VBICSEXPORT'); ?></span></a>
	</div>
	<div class="btn-group pull-left input-append">
		<input type="text" name="confirmnumber" id="confirmnumber" autocomplete="off" placeholder="<?php echo JText::translate('VBOFILTCONFNUMCUST'); ?>" value="<?php echo (strlen($pconfirmnumber) > 0 ? $pconfirmnumber : ''); ?>" size="30" />
		<button type="submit" class="btn"><i class="icon-search"></i></button>
	</div>
	<?php
	$cust_id_filter = false;
	if (is_array($rows) && isset($rows[0]) && array_key_exists('customer_fullname', $rows[0])) {
		//customer ID filter
		$cust_id_filter = true;
	}
	?>
	<div class="btn-group pull-left input-append">
		<a style="display: none;" id="vbo-unset-cust-filter" href="index.php?option=com_vikbooking&task=orders&cust_id=0"></a>
		<div id="vbo-allbsearchcust-res" class="vbo-allbsearchcust-res" style="display: none;"></div>
		<input type="text" id="customernominative" autocomplete="off" placeholder="<?php echo JText::translate('VBCUSTOMERNOMINATIVE'); ?>" value="<?php echo $cust_id_filter ? htmlspecialchars($rows[0]['customer_fullname']) : ''; ?>" size="30" />
		<button type="button" class="btn" onclick="<?php echo $cust_id_filter ? 'document.location.href=jQuery(\'#vbo-unset-cust-filter\').attr(\'href\');' : 'document.getElementById(\'customernominative\').focus();'; ?>"><i class="<?php echo $cust_id_filter ? 'icon-remove' : 'icon-user'; ?>"></i></button>
	</div>
	<div class="btn-group pull-left">
		<button type="button" class="btn" id="vbo-search-tools-btn" onclick="if(jQuery(this).hasClass('btn-primary')){jQuery('#vbo-search-tools-cont').hide();jQuery(this).removeClass('btn-primary');}else{jQuery('#vbo-search-tools-cont').show();jQuery(this).addClass('btn-primary');}"><?php echo JText::translate('JSEARCH_TOOLS'); ?> <span class="caret"></span></button>
	</div>
	<div class="btn-group pull-left">
		<button type="button" class="btn" onclick="vboClearFilters();"><?php echo JText::translate('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>

	<div id="vbo-search-tools-cont" class="js-stools-container-filters clearfix" style="display: none;">
		<div class="btn-group pull-left">
			<select name="channel">
				<option value=""><?php echo JText::translate('VBCHANNELFILTER'); ?></option>
		<?php
		$pchannel = $app->getUserStateFromRequest("vbo.orders.channel", 'channel', '', 'string');
		if (count($all_channels) > 0) {
			$filters_set = !empty($pchannel) || $filters_set;
			?>
				<option value="-1"<?php echo $pchannel == '-1' ? ' selected="selected"' : ''; ?>>- <?php echo JText::translate('VBORDFROMSITE'); ?></option>
			<?php
			foreach ($all_channels as $o_channel) {
				?>
				<option value="<?php echo $o_channel; ?>"<?php echo $pchannel == $o_channel ? ' selected="selected"' : ''; ?>>- <?php echo ucwords($o_channel); ?></option>
				<?php
			}
		}
		?>
			</select>
		</div>
		<div class="btn-group pull-left">
		<?php
		$pidroom = $app->getUserStateFromRequest("vbo.orders.idroom", 'idroom', 0, 'int');
		if (count($allrooms) > 0) {
			$filters_set = !empty($pidroom) || $filters_set;
			$rsel = '<select name="idroom"><option value="">'.JText::translate('VBROOMFILTER').'</option>';
			foreach ($allrooms as $room) {
				$rsel .= '<option value="'.$room['id'].'"'.(!empty($pidroom) && $pidroom == $room['id'] ? ' selected="selected"' : '').'>'.$room['name'].'</option>';
			}
			$rsel .= '</select>';
		}
		echo $rsel;
		?>
		</div>
	<?php
	$pcategory_id = $app->getUserStateFromRequest("vbo.orders.category_id", 'category_id', 0, 'int');
	if (count($this->categories)) {
		$filters_set = !empty($pcategory_id) || $filters_set;
		?>
		<div class="btn-group pull-left">
			<select name="category_id">
				<option value=""><?php echo JText::translate('VBOCATEGORYFILTER'); ?></option>
			<?php
			foreach ($this->categories as $catid => $catname) {
				?>
				<option value="<?php echo $catid; ?>"<?php echo $catid == $pcategory_id ? ' selected="selected"' : ''; ?>><?php echo $catname; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<?php
	}
	?>
		<div class="btn-group pull-left">
			<select name="idpayment">
				<option value=""><?php echo JText::translate('VBOFILTERBYPAYMENT'); ?></option>
			<?php
			$pidpayment = $app->getUserStateFromRequest("vbo.orders.idpayment", 'idpayment', 0, 'int');
			$payment_filter = '';
			if (!empty($pidpayment)) {
				$filters_set = !empty($pidpayment) || $filters_set;
				$payment_filter = '&amp;idpayment='.$pidpayment;
			}
			$allpayments = array();
			$q = "SELECT `id`,`name` FROM `#__vikbooking_gpayments` ORDER BY `name` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$allpayments = $dbo->loadAssocList();
			}
			foreach ($allpayments as $paym) {
				?>
				<option value="<?php echo $paym['id']; ?>"<?php echo $paym['id'] == $pidpayment ? ' selected="selected"' : ''; ?>><?php echo $paym['name']; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<div class="btn-group pull-left">
			<select name="status">
				<option value=""><?php echo JText::translate('VBOFILTERBYSTATUS'); ?></option>
			<?php
			$pstatus = $app->getUserStateFromRequest("vbo.orders.status", 'status', '', 'string');
			$filters_set = !empty($pstatus) || $filters_set;
			$status_filter = !empty($pstatus) ? '&amp;status='.$pstatus : '';
			?>
				<optgroup label="<?php echo JText::translate('VBSTATUS'); ?>">
					<option value="confirmed"<?php echo $pstatus == 'confirmed' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBCONFIRMED'); ?></option>
					<option value="standby"<?php echo $pstatus == 'standby' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBSTANDBY'); ?></option>
					<option value="cancelled"<?php echo $pstatus == 'cancelled' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBCANCELLED'); ?></option>
					<option value="closure"<?php echo $pstatus == 'closure' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBDBTEXTROOMCLOSED'); ?></option>
					<option value="inquiry"<?php echo $pstatus == 'inquiry' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBO_BTYPE_INQUIRY'); ?></option>
					<option value="request"<?php echo $pstatus == 'request' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBO_BTYPE_REQUEST'); ?></option>
				</optgroup>
				<optgroup label="<?php echo JText::translate('VBOCHECKEDSTATUS'); ?>">
					<option value="checkedin"<?php echo $pstatus == 'checkedin' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBOCHECKEDSTATUSIN'); ?></option>
					<option value="checkedout"<?php echo $pstatus == 'checkedout' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBOCHECKEDSTATUSOUT'); ?></option>
					<option value="noshow"<?php echo $pstatus == 'noshow' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBOCHECKEDSTATUSNOS'); ?></option>
					<option value="none"<?php echo $pstatus == 'none' ? ' selected="selected"' : ''; ?>><?php echo JText::translate('VBOCHECKEDSTATUSZERO'); ?></option>
				</optgroup>
			</select>
		</div>
		<div class="btn-group pull-left">
		<?php
		$dates_filter = '';
		$pdatefilt = $app->getUserStateFromRequest("vbo.orders.datefilt", 'datefilt', 0, 'int');
		$pdatefiltfrom = $app->getUserStateFromRequest("vbo.orders.datefiltfrom", 'datefiltfrom', '', 'string');
		$pdatefiltto = $app->getUserStateFromRequest("vbo.orders.datefiltto", 'datefiltto', '', 'string');
		if (!empty($pdatefilt) && (!empty($pdatefiltfrom) || !empty($pdatefiltto))) {
			$filters_set = true;
			$dates_filter = '&amp;datefilt='.$pdatefilt.(!empty($pdatefiltfrom) ? '&amp;datefiltfrom='.$pdatefiltfrom : '').(!empty($pdatefiltto) ? '&amp;datefiltto='.$pdatefiltto : '');
		}
		$datesel = '<select name="datefilt" onchange="vboToggleDateFilt(this.value);"><option value="">'.JText::translate('VBOFILTERBYDATES').'</option>';
		$datesel .= '<option value="1"'.(!empty($pdatefilt) && $pdatefilt == 1 ? ' selected="selected"' : '').'>'.JText::translate('VBOFILTERDATEBOOK').'</option>';
		$datesel .= '<option value="2"'.(!empty($pdatefilt) && $pdatefilt == 2 ? ' selected="selected"' : '').'>'.JText::translate('VBOFILTERDATEIN').'</option>';
		$datesel .= '<option value="3"'.(!empty($pdatefilt) && $pdatefilt == 3 ? ' selected="selected"' : '').'>'.JText::translate('VBOFILTERDATEOUT').'</option>';
		$datesel .= '</select>';
		echo $datesel;
		?>
		</div>
		<div class="btn-group pull-left" id="vbo-dates-cont" style="display: <?php echo (!empty($pdatefilt) && (!empty($pdatefiltfrom) || !empty($pdatefiltto)) ? 'inline-block' : 'none'); ?>;">
			<input type="text" id="vbo-date-from" placeholder="<?php echo JText::translate('VBNEWSEASONONE'); ?>" value="<?php echo $pdatefiltfrom; ?>" size="10" name="datefiltfrom" autocomplete="off" />&nbsp;-&nbsp;<input type="text" id="vbo-date-to" placeholder="<?php echo JText::translate('VBNEWSEASONTWO'); ?>" value="<?php echo $pdatefiltto; ?>" size="10" name="datefiltto" autocomplete="off" />
		</div>
		<div class="btn-group pull-left">
			<button type="submit" class="btn"><i class="icon-search"></i> <?php echo JText::translate('VBPVIEWORDERSSEARCHSUBM'); ?></button>
		</div>
	</div>
</div>

<div class="table-responsive">
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-bookingslist-table">
	<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title center" width="20" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vborderby=id&amp;vbordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vbo-bookingslist-activesort" : ($orderby == "id" ? "vbo-bookingslist-activesort" : "")); ?>">
					<?php echo 'ID'.($orderby == "id" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "id" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="110">
				<a href="index.php?option=com_vikbooking&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vborderby=ts&amp;vbordersort=<?php echo ($orderby == "ts" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "ts" && $ordersort == "ASC" ? "vbo-bookingslist-activesort" : ($orderby == "ts" ? "vbo-bookingslist-activesort" : "")); ?>">
					<?php echo JText::translate('VBPVIEWORDERSONE').($orderby == "ts" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "ts" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="200"><span><?php echo JText::translate('VBPVIEWORDERSTWO'); ?></span></th>
			<th class="title center" width="100" align="center"><span><?php echo JText::translate('VBPVIEWORDERSTHREE'); ?></span></th>
			<th class="title left" width="140"><span><?php echo JText::translate('VBPVIEWORDERSPEOPLE'); ?></span></th>
			<th class="title left" width="110">
				<a href="index.php?option=com_vikbooking&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vborderby=checkin&amp;vbordersort=<?php echo ($orderby == "checkin" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "checkin" && $ordersort == "ASC" ? "vbo-bookingslist-activesort" : ($orderby == "checkin" ? "vbo-bookingslist-activesort" : "")); ?>">
					<?php echo JText::translate('VBPVIEWORDERSFOUR').($orderby == "checkin" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "checkin" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
				</th>
			<th class="title left" width="110">
				<a href="index.php?option=com_vikbooking&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vborderby=checkout&amp;vbordersort=<?php echo ($orderby == "checkout" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "checkout" && $ordersort == "ASC" ? "vbo-bookingslist-activesort" : ($orderby == "checkout" ? "vbo-bookingslist-activesort" : "")); ?>">
					<?php echo JText::translate('VBPVIEWORDERSFIVE').($orderby == "checkout" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "checkout" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="60" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vborderby=days&amp;vbordersort=<?php echo ($orderby == "days" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "days" && $ordersort == "ASC" ? "vbo-bookingslist-activesort" : ($orderby == "days" ? "vbo-bookingslist-activesort" : "")); ?>">
					<?php echo JText::translate('VBPVIEWORDERSSIX').($orderby == "days" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "days" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="110" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=orders<?php echo ($cust_id_filter ? '&amp;cust_id='.$pcust_id : '').$dates_filter.$status_filter.$payment_filter; ?>&amp;vborderby=total&amp;vbordersort=<?php echo ($orderby == "total" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "total" && $ordersort == "ASC" ? "vbo-bookingslist-activesort" : ($orderby == "total" ? "vbo-bookingslist-activesort" : "")); ?>">
					<?php echo JText::translate('VBPVIEWORDERSSEVEN').($orderby == "total" && $ordersort == "ASC" ? '<i class="'.VikBookingIcons::i('sort-asc').'"></i>' : ($orderby == "total" ? '<i class="'.VikBookingIcons::i('sort-desc').'"></i>' : '<i class="'.VikBookingIcons::i('sort').'"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="30" align="center"><span>&nbsp;</span></th>
			<th class="title center" width="30" align="center"><span>&nbsp;</span></th>
			<th class="title center" width="100" align="center"><span><?php echo JText::translate('VBPVIEWORDERSEIGHT'); ?></span></th>
			<th class="title center" width="100" align="center"><span><?php echo JText::translate('VBPVIEWORDERCHANNEL'); ?></span></th>
		</tr>
	</thead>
	<?php
	$currencysymb = VikBooking::getCurrencySymb(true);
	$monsmap = array(
		JText::translate('VBSHORTMONTHONE'),
		JText::translate('VBSHORTMONTHTWO'),
		JText::translate('VBSHORTMONTHTHREE'),
		JText::translate('VBSHORTMONTHFOUR'),
		JText::translate('VBSHORTMONTHFIVE'),
		JText::translate('VBSHORTMONTHSIX'),
		JText::translate('VBSHORTMONTHSEVEN'),
		JText::translate('VBSHORTMONTHEIGHT'),
		JText::translate('VBSHORTMONTHNINE'),
		JText::translate('VBSHORTMONTHTEN'),
		JText::translate('VBSHORTMONTHELEVEN'),
		JText::translate('VBSHORTMONTHTWELVE')
	);
	$vcm_logos = VikBooking::getVcmChannelsLogo('', true);
	$kk = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		if (!$row['closure']) {
			array_push($vcm_bids, $row['id']);
		}
		$rooms = VikBooking::loadOrdersRoomsData($row['id']);
		$peoplestr = "";
		$room_names = array();
		if (is_array($rooms)) {
			$totadults = 0;
			$totchildren = 0;
			foreach($rooms as $rr) {
				$totadults += $rr['adults'];
				$totchildren += $rr['children'];
				$room_names[] = $rr['room_name'];
			}
			$peoplestr .= $totadults." ".($totadults > 1 ? JText::translate('VBMAILADULTS') : JText::translate('VBMAILADULT')).($totchildren > 0 ? ", ".$totchildren." ".($totchildren > 1 ? JText::translate('VBMAILCHILDREN') : JText::translate('VBMAILCHILD')) : "");
		}
		$isdue = $row['total'];
		$otachannel = '';
		$otacurrency = '';
		if (!empty($row['channel'])) {
			$channelparts = explode('_', $row['channel']);
			$otachannel = array_key_exists(1, $channelparts) && strlen($channelparts[1]) > 0 ? $channelparts[1] : ucwords($channelparts[0]);
			$otachannelclass = $otachannel;
			if (strstr($otachannelclass, '.') !== false) {
				$otaccparts = explode('.', $otachannelclass);
				$otachannelclass = $otaccparts[0];
			}
			$otacurrency = strlen($row['chcurrency']) > 0 ? $row['chcurrency'] : '';
		}
		//Customer Details
		$custdata = $row['custdata'];
		$custdata_parts = explode("\n", $row['custdata']);
		if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
			//get the first two fields
			$custvalues = array();
			foreach ($custdata_parts as $custdet) {
				if (strlen($custdet) < 1) {
					continue;
				}
				$custdet_parts = explode(':', $custdet);
				if (count($custdet_parts) >= 2) {
					unset($custdet_parts[0]);
					array_push($custvalues, trim(implode(':', $custdet_parts)));
				}
				if (count($custvalues) > 1) {
					break;
				}
			}
			if (count($custvalues) > 1) {
				$custdata = implode(' ', $custvalues);
			}
		}
		if (strlen($custdata) > 45) {
			$custdata = substr($custdata, 0, 45)." ...";
		}
		$q = "SELECT `c`.*,`co`.`idorder` FROM `#__vikbooking_customers` AS `c` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cust_country = $dbo->loadAssocList();
			$cust_country = $cust_country[0];
			if (!empty($cust_country['first_name'])) {
				$custdata = $cust_country['first_name'].' '.$cust_country['last_name'];
				if (!empty($cust_country['country'])) {
					if (file_exists(VBO_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$cust_country['country'].'.png')) {
						$custdata .= '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$cust_country['country'].'.png'.'" title="'.$cust_country['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
					}
				}
			}
		}
		$custdata = $row['closure'] > 0 || JText::translate('VBDBTEXTROOMCLOSED') == $row['custdata'] ? '<span class="vbordersroomclosed"><i class="'.VikBookingIcons::i('ban').'"></i> '.JText::translate('VBDBTEXTROOMCLOSED').'</span>' : $custdata;
		//
		if ($row['status'] == "confirmed") {
			//$saystaus = "<span style=\"color: #4ca25a; font-weight: bold;\">".JText::translate('VBCONFIRMED')."</span>";
			$saystaus = '<span class="label label-success vbo-status-label">'.JText::translate('VBCONFIRMED').'</span>';
		} elseif ($row['status'] == "standby") {
			//$saystaus = "<span style=\"color: #e0a504; font-weight: bold;\">".JText::translate('VBSTANDBY')."</span>";
			$status_type = !empty($row['type']) ? JText::translate('VBO_BTYPE_' . strtoupper($row['type'])) : JText::translate('VBSTANDBY');
			$saystaus = '<span class="label label-warning vbo-status-label">' . $status_type . '</span>';
		} else {
			//$saystaus = "<span class=\"vbordcancelled\">".JText::translate('VBCANCELLED')."</span>";
			$saystaus = '<span class="label label-error vbo-status-label" style="background-color: #d9534f;">'.JText::translate('VBCANCELLED').'</span>';
		}
		$ts_info = getdate($row['ts']);
		$ts_wday = JText::translate('VB'.strtoupper(substr($ts_info['weekday'], 0, 3)));
		$checkin_info = getdate($row['checkin']);
		$checkin_wday = JText::translate('VB'.strtoupper(substr($checkin_info['weekday'], 0, 3)));
		$checkout_info = getdate($row['checkout']);
		$checkout_wday = JText::translate('VB'.strtoupper(substr($checkout_info['weekday'], 0, 3)));
		?>
	
		<tr class="row<?php echo $kk; ?>">
			<td class="skip">
				<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);">
			</td>
			<td class="center">
				<a class="vbo-bookingid" href="index.php?option=com_vikbooking&amp;task=editorder&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a>
			</td>
			<td>
				<a class="vbo-bookingslist-viewdet-link" href="index.php?option=com_vikbooking&amp;task=editorder&amp;cid[]=<?php echo $row['id']; ?>">
					<div class="vbo-bookingslist-viewdet">
						<div class="vbo-bookingslist-viewdet-open">
							<?php VikBookingIcons::e('external-link'); ?>
						</div>
						<div class="vbo-bookingslist-viewdet-fulldate">
							<div class="vbo-bookingslist-viewdet-date">
							<?php
							if (strpos($df, 'd') < strpos($df, 'm')) {
								//assuming d/m/Y or similar
								?>
								<span><?php echo $ts_info['mday']; ?></span>
								<span><?php echo $monsmap[($ts_info['mon'] - 1)]; ?></span>
								<?php
							} else {
								//assuming m/d/Y or similar
								?>
								<span><?php echo $monsmap[($ts_info['mon'] - 1)]; ?></span>
								<span><?php echo $ts_info['mday']; ?></span>
								<?php
							}
							?>
								<span><?php echo $ts_info['year']; ?></span>
							</div>
							<div class="vbo-bookingslist-viewdet-time">
								<span class="vbo-bookingslist-viewdet-wday"><?php echo $ts_wday; ?></span>
								<span class="vbo-bookingslist-viewdet-hour"><?php echo date('H:i', $row['ts']); ?></span>
							</div>
						</div>
					</div>
				</a>
			</td>
			<td>
				<?php echo $custdata; ?>
				<span class="vbo-orders-review-link" style="display: none;" title="<?php echo addslashes(JText::translate('VBOSEEGUESTREVIEW')); ?>" data-bid="<?php echo $row['id']; ?>"></span>
			</td>
			<td class="center">
				<?php
				if (count($room_names) > 1) {
					?>
					<span class="hasTooltip vbo-tip-small vbo-bookingslist-numrooms" title="<?php echo implode(', ', $room_names); ?>"><?php echo $row['roomsnum']; ?></span>
					<?php
				} else {
					?>
					<span class="vbo-bookingslist-roomname"><?php echo $row['roomsnum'] == 1 && count($room_names) > 0 ? $room_names[0] : $row['roomsnum']; ?></span>
					<?php
				}
				?>
			</td>
			<td>
				<?php echo $peoplestr; ?>
			</td>
			<td>
				<div class="vbo-bookingslist-booktime vbo-bookingslist-booktime-checkin">
					<div class="vbo-bookingslist-booktime-fulldate">
						<div class="vbo-bookingslist-booktime-date">
							<span><?php echo date(str_replace("/", $datesep, $df), $row['checkin']); ?></span>
						</div>
						<div class="vbo-bookingslist-booktime-time">
							<span class="vbo-bookingslist-booktime-twrap">
								<span class="vbo-bookingslist-booktime-wday"><?php echo $checkin_wday; ?></span>
								<span class="vbo-bookingslist-booktime-hour"><?php echo date('H:i', $row['checkin']); ?></span>
							</span>
						<?php
						if ($row['checked'] == 1) {
							//checked in
							?>
							<span class="vbo-bookingslist-booktime-checkedin"><i class="<?php echo VikBookingIcons::i('circle', 'hasTooltip'); ?>" title="<?php echo JText::translate('VBOCHECKEDSTATUSIN'); ?>"></i></span>
							<?php
						} elseif ($row['checked'] < 0) {
							//no show
							?>
							<span class="vbo-bookingslist-booktime-noshow"><i class="<?php echo VikBookingIcons::i('circle', 'hasTooltip'); ?>" title="<?php echo JText::translate('VBOCHECKEDSTATUSNOS'); ?>"></i></span>
							<?php
						}
						?>
						</div>
					</div>
				</div>
			</td>
			<td>
				<div class="vbo-bookingslist-booktime vbo-bookingslist-booktime-checkin">
					<div class="vbo-bookingslist-booktime-fulldate">
						<div class="vbo-bookingslist-booktime-date">
							<span><?php echo date(str_replace("/", $datesep, $df), $row['checkout']); ?></span>
						</div>
						<div class="vbo-bookingslist-booktime-time">
							<span class="vbo-bookingslist-booktime-twrap">
								<span class="vbo-bookingslist-booktime-wday"><?php echo $checkout_wday; ?></span>
								<span class="vbo-bookingslist-booktime-hour"><?php echo date('H:i', $row['checkout']); ?></span>
							</span>
						<?php
						if ($row['checked'] == 2) {
							//checked out
							?>
							<span class="vbo-bookingslist-booktime-checkedout"><i class="<?php echo VikBookingIcons::i('circle', 'hasTooltip'); ?>" title="<?php echo JText::translate('VBOCHECKEDSTATUSOUT'); ?>"></i></span>
							<?php
						}
						?>
						</div>
					</div>
				</div>
			</td>
			<td class="center">
				<span class="vbo-bookingslist-numnights"><?php echo $row['days']; ?></span>
			</td>
			<td class="center">
				<div class="vbo-bookingslist-total-wrap">
					<div class="vbo-bookingslist-total-amount">
						<span><?php echo strlen($otacurrency) > 0 ? $otacurrency : $currencysymb; ?></span>
						<span><?php echo VikBooking::numberFormat($isdue); ?></span>
					</div>
				<?php
				if (strlen($row['totpaid'])) {
					?>
					<div class="vbo-bookingslist-total-totpaid">
						<span><?php echo $currencysymb; ?></span>
						<span><?php echo VikBooking::numberFormat($row['totpaid']); ?></span>
					<?php
					if ($row['refund'] > 0) {
						?>
						<span class="vbo-bookingslist-total-refunded">
							<span><?php echo $currencysymb; ?></span>
							<span><?php echo VikBooking::numberFormat($row['refund']); ?></span>
						</span>
						<?php
					}
					?>
					</div>
					<?php
				} elseif ($row['refund'] > 0) {
					?>
					<div class="vbo-bookingslist-total-totpaid">
						<span class="vbo-bookingslist-total-refunded">
							<span><?php echo $currencysymb; ?></span>
							<span><?php echo VikBooking::numberFormat($row['refund']); ?></span>
						</span>
					</div>
					<?php
				}
				?>
				</div>
			</td>
			<td class="center">
			<?php
			$bcolortag = VikBooking::applyBookingColorTag($row, $colortags);
			if (count($bcolortag) > 0) {
				$bcolortag['name'] = JText::translate($bcolortag['name']);
				?>
				<div class="vbo-colortag-circle hasTooltip" style="background-color: <?php echo $bcolortag['color']; ?>;" title="<?php echo $bcolortag['name']; ?>" data-ctagcolor="<?php echo $bcolortag['color']; ?>" data-bid="<?php echo $row['id']; ?>"></div>
				<?php
			}
			?>
			</td>
			<td class="center">
				<?php
				echo (!empty($row['adminnotes']) ? '<span class="hasTooltip vbo-admin-tipsicon" title="'.htmlentities(nl2br($row['adminnotes'])).'"><i class="'.VikBookingIcons::i('sticky-note').'"></i></span>&nbsp;' : '');
				echo (file_exists(VBO_SITE_PATH . DS . "helpers" . DS . "invoices" . DS . "generated" . DS . $row['id'].'_'.$row['sid'] .".pdf") ? '<a class="hasTooltip vbo-admin-invoiceicon" href="'.VBO_SITE_URI.'helpers/invoices/generated/'.$row['id'].'_'.$row['sid'].'.pdf" target="_blank" title="'.JText::translate('VBOINVDOWNLOAD').'"><i class="'.VikBookingIcons::i('invoice').'"></i></a>' : '');
				?>
				<span class="vbo-bookings-guestmessages-bubble-cont vbo-admin-tipsicon" style="display: none;" data-bid="<?php echo $row['id']; ?>">
					<a href="index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $row['id']; ?>#messaging">
						<i class="<?php echo VikBookingIcons::i('commenting'); ?>" data-message-count="0"></i>
					</a>
				</span>
			</td>
			<td class="center">
				<?php echo $saystaus; ?>
			</td>
			<td class="center">
			<?php
			if (!empty($row['channel'])) {
				//VBO 1.10: use of strtolower($otachannelclass) next to the class 'vbotasp' is deprecated. Fetch the OTA logo from VCM
				$ota_logo_img = is_object($vcm_logos) ? $vcm_logos->setProvenience($otachannel, $row['channel'])->getLogoURL() : false;
				if ($ota_logo_img !== false) {
					?>
				<img src="<?php echo $ota_logo_img; ?>" class="vbo-channelimg-medium"/>
					<?php
				} else {
					?>
				<span class="vbo-provenience"><?php echo $otachannel; ?></span>
					<?php
				}
			} else {
				?>
				<span class="vbo-provenience"><?php echo JText::translate('VBORDFROMSITE'); ?></span>
				<?php
			}
			?>
			</td>
		</tr>
		<?php
		$kk = 1 - $kk;
		
	}
	?>
	
</table>
</div>
<input type="hidden" name="option" value="com_vikbooking" />
<input type="hidden" name="cust_id" id="cust_id" value="<?php echo !empty($pcust_id) ? $pcust_id : ''; ?>" />
<input type="hidden" name="task" value="orders" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHtml::fetch( 'form.token' ); ?>
<?php echo $navbut; ?>
</form>

<a style="display: none;" id="vcm-review-details-base" href="index.php?option=com_vikchannelmanager&task=reviews&revid="></a>

<script type="text/javascript">
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
} else {
	jQuery.fn.tooltip = function(){};
}
function vboToggleDateFilt(dtype) {
	if (!(dtype.length > 0)) {
		document.getElementById('vbo-dates-cont').style.display = 'none';
		document.getElementById('vbo-date-from').value = '';
		document.getElementById('vbo-date-to').value = '';
		return true;
	}
	document.getElementById('vbo-dates-cont').style.display = 'inline-block';
	return true;
}
var bctags_tip = "<?php echo $bctags_tip; ?>";
var applying_tag = false;
var bctags_tip_on = false;
jQuery(document.body).on('click', '.vbo-colortag-circle', function() {
	if (!jQuery(this).parent().find(".vbo-blist-tip-bctag-subtip").length) {
		jQuery(".vbo-blist-tip-bctag-subtip").remove();
		var cur_color = jQuery(this).attr("data-ctagcolor");
		var cur_bid = jQuery(this).attr("data-bid");
		jQuery(this).after("<div class=\"vbo-blist-tip-bctag-subtip\">"+bctags_tip+"</div>");
		jQuery(this).parent().find(".vbo-blist-tip-bctag-subtip").find(".vbo-blist-tip-bctag-subtip-circle[data-ctagcolor='"+cur_color+"']").addClass("vbo-blist-tip-bctag-activecircle").css('border-color', cur_color);
		jQuery(this).parent().find(".vbo-blist-tip-bctag-subtip").find(".vbo-blist-tip-bctag-subtip-circle").attr('data-bid', cur_bid);
		jQuery(".vbo-blist-tip-bctag-subtip .hasTooltip").tooltip();
		bctags_tip_on = true;
	} else {
		jQuery(".vbo-blist-tip-bctag-subtip").remove();
		bctags_tip_on = false;
	}
});
jQuery(document.body).on('click', '.vbo-blist-tip-bctag-subtip-circle', function() {
	if (applying_tag === true) {
		return false;
	}
	applying_tag = true;
	var clickelem = jQuery(this);
	var ctagkey = clickelem.attr('data-ctagkey');
	var bid = clickelem.attr('data-bid');
	//set opacity to circles as loading
	jQuery('.vbo-blist-tip-bctag-subtip-circle').css('opacity', '0.6');
	//
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikbooking", task: "setbookingtag", tmpl: "component", idorder: bid, tagkey: ctagkey }
	}).done(function(res) {
		applying_tag = false;
		if (res.indexOf('e4j.error') >= 0 ) {
			console.log(res);
			alert(res.replace("e4j.error.", ""));
			//restore loading opacity in circles
			jQuery('.vbo-blist-tip-bctag-subtip-circle').css('opacity', '1');
		} else {
			var obj_res = JSON.parse(res);
			jQuery(clickelem).closest(".vbo-blist-tip-bctag-subtip").parent().find(".vbo-colortag-circle").css("background-color", obj_res.color).attr('data-ctagcolor', obj_res.color).attr('data-original-title', obj_res.name);
			jQuery(".vbo-blist-tip-bctag-subtip").remove();
			bctags_tip_on = false;
		}
	}).fail(function() {
		applying_tag = false;
		alert("Request Failed");
		//restore loading opacity in circles
		jQuery('.vbo-blist-tip-bctag-subtip-circle').css('opacity', '1');
	});
});
jQuery(document).ready(function() {
	jQuery('.vbo-bookingslist-viewdet-link').click(function(e) {
		if (e && e.target.tagName.toUpperCase() == 'I') {
			//open the link in a new window
			e.preventDefault();
			window.open(jQuery(this).attr('href'), '_blank');
		}
	});
	jQuery('#vbo-date-from').datepicker({
		showOn: 'focus',
		dateFormat: '<?php echo $juidf; ?>',
		onSelect: function( selectedDate ) {
			jQuery('#vbo-date-to').datepicker('option', 'minDate', selectedDate);
		}
	});
	jQuery('#vbo-date-to').datepicker({
		showOn: 'focus',
		dateFormat: '<?php echo $juidf; ?>',
		onSelect: function( selectedDate ) {
			jQuery('#vbo-date-from').datepicker('option', 'maxDate', selectedDate);
		}
	});
	//Search customer - Start
	var vbocustsdelay = (function() {
		var timer = 0;
		return function(callback, ms) {
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();
	function vboCustomerSearch(words) {
		jQuery("#vbo-allbsearchcust-res").hide().html("");
		jQuery("#customernominative").addClass('vbo-allbsearchcust-loading-inp');
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikbooking", task: "searchcustomer", kw: words, nopin: 1, tmpl: "component" }
		}).done(function(cont) {
			if (cont.length) {
				var obj_res = JSON.parse(cont);
				jQuery("#vbo-allbsearchcust-res").html(obj_res[1]);
			} else {
				jQuery("#vbo-allbsearchcust-res").html("");
			}
			jQuery("#vbo-allbsearchcust-res").show();
			jQuery("#customernominative").removeClass('vbo-allbsearchcust-loading-inp');
		}).fail(function() {
			jQuery("#customernominative").removeClass('vbo-allbsearchcust-loading-inp');
			alert("Error Searching.");
		});
	}
	jQuery("#customernominative").keyup(function(event) {
		vbocustsdelay(function() {
			var keywords = jQuery("#customernominative").val();
			if (keywords.length > 1) {
				if ((event.which > 96 && event.which < 123) || (event.which > 64 && event.which < 91) || event.which == 13) {
					vboCustomerSearch(keywords);
				}
			} else {
				if (jQuery("#vbo-allbsearchcust-res").is(":visible")) {
					jQuery("#vbo-allbsearchcust-res").hide();
				}
			}
		}, 600);
	});
	jQuery(document).on('click', '.vbo-custsearchres-entry', function() {
		var customer_id = jQuery(this).attr('data-custid');
		if (customer_id.length) {
			document.location.href = 'index.php?option=com_vikbooking&task=orders&cust_id='+customer_id;
		}
	});
	//Search customer - End
	jQuery(document).keydown(function(e) {
		if ( e.keyCode == 27 && bctags_tip_on === true ) {
			jQuery(".vbo-blist-tip-bctag-subtip").remove();
			bctags_tip_on = false;
		}
		if (e.keyCode == 13) {
			//prevent form-submit by hitting enter
			e.preventDefault();
			return false;
		}
	});
	jQuery(document).mouseup(function(e) {
		if (!bctags_tip_on) {
			return false;
		}
		if (jQuery(".vbo-blist-tip-bctag-subtip").length) {
			var vbo_overlay_subtip_cont = jQuery(".vbo-blist-tip-bctag-subtip-inner");
			if (!vbo_overlay_subtip_cont.is(e.target) && vbo_overlay_subtip_cont.has(e.target).length === 0) {
				jQuery(".vbo-blist-tip-bctag-subtip").remove();
				bctags_tip_on = false;
				return true;
			}
		}
	});
	jQuery(".vbo-bookingslist-table tr td").not(".skip").click(function() {
		//the checkbox for the booking is on the first TD of the row
		var trcbox = jQuery(this).parent("tr").find("td").first().find("input[type='checkbox']");
		if (!trcbox || !trcbox.length) {
			return;
		}
		trcbox.prop('checked', !(trcbox.prop('checked')));
		if (typeof Joomla !== 'undefined' && Joomla != null) {
			Joomla.isChecked(trcbox.prop('checked'));
		}
	});
	jQuery(".vbo-bookingslist-table tr").dblclick(function() {
		if (document.selection && document.selection.empty) {
			document.selection.empty();
		} else if (window.getSelection) {
			var sel = window.getSelection();
			sel.removeAllRanges();
		}
		//the link to the booking details page is on the third TD of the row
		var olink = jQuery(this).find("td").first().next().next().find("a");
		if (!olink || !olink.length) {
			return;
		}
		document.location.href = olink.attr("href");
	});
	<?php
	if ($filters_set) {
		?>
	jQuery("#vbo-search-tools-btn").trigger("click");
		<?php
	}
	?>
	vcmCountGuestMessages();
	setInterval(function() {
		vcmCountGuestMessages();
	}, 15000);
	// load guest reviews
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: {
			option: "com_vikbooking",
			task: "bookings_have_reviews",
			tmpl: "component",
			bids: <?php echo json_encode($vcm_bids); ?>
		}
	}).done(function(res) {
		try {
			var obj = JSON.parse(res);
			var base_link = jQuery('#vcm-review-details-base').attr('href');
			for (var bid in obj) {
				if (!obj.hasOwnProperty(bid)) {
					continue;
				}
				jQuery('.vbo-orders-review-link[data-bid="' + bid + '"]').html('<a href="' + base_link + obj[bid] + '" target="_blank"><?php VikBookingIcons::e('star'); ?></a>').fadeIn();
			}
		}
		catch(err) {
			console.error(err);
		}
	}).fail(function() {
		console.log('request failed for loading booking reviews');
	});
});
function vcmCountGuestMessages() {
	<?php
	// VCM Chat Guest Messages (if VCM is installed)
	if (count($vcm_bids) && is_file(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php')) {
		?>
	var all_bids = <?php echo json_encode($vcm_bids); ?>;
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: {
			option: "com_vikchannelmanager",
			task: "chat.count_unread_messages",
			tmpl: "component",
			id_order: all_bids
		}
	}).done(function(res) {
		var counters = JSON.parse(res);
		for (var bid in all_bids) {
			if (!all_bids.hasOwnProperty(bid)) {
				continue;
			}
			var booking_bubble = jQuery('.vbo-bookings-guestmessages-bubble-cont[data-bid="'+all_bids[bid]+'"]');
			if (!booking_bubble || !booking_bubble.length) {
				continue;
			}
			if (!counters.hasOwnProperty(all_bids[bid])) {
				// 0 messages to be read
				booking_bubble.find('i').attr('data-message-count', '0');
				booking_bubble.hide();
				continue;
			}
			booking_bubble.find('i').attr('data-message-count', counters[all_bids[bid]]);
			if (parseInt(counters[all_bids[bid]]) > 0) {
				booking_bubble.fadeIn();
			} else {
				booking_bubble.hide();
			}
		}
	}).fail(function() {
		console.error('AJAX request failed for counting guest messages from VCM');
	});
		<?php
	}
	?>
}
</script>
<?php

//Invoices
$pconfirmgen = VikRequest::getInt('confirmgen', '', 'request');
if (count($cid) > 0 && !empty($cid[0])) {
	$oldinvdate = '';
	$nextinvnum = VikBooking::getNextInvoiceNumber();
	$invsuff = VikBooking::getInvoiceNumberSuffix();
	$companyinfo = VikBooking::getInvoiceCompanyInfo();
	//if editing an invoice (re-creating an existing invoice for a booking), do not increment the invoice number
	if (count($cid) == 1) {
		//the generate invoice btn of the booking details page can pass the invoice notes field via hidden field. If not empty, update invoice notes for this id
		$pinvnotes = VikRequest::getString('invnotes', '', 'request', VIKREQUEST_ALLOWHTML);
		if (!empty($pinvnotes)) {
			$pinvnotes = strpos($pinvnotes, '<br') !== false ? $pinvnotes : nl2br($pinvnotes);
			$q = "UPDATE `#__vikbooking_orders` SET `inv_notes`=".$dbo->quote($pinvnotes)." WHERE `id`=".(int)$cid[0].";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		$q = "SELECT `number`,`for_date` FROM `#__vikbooking_invoices` WHERE `idorder`=".(int)$cid[0].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$prev_data = $dbo->loadAssoc();
			$oldinvdate = $prev_data['for_date'];
			$prev_inv_number = intval(str_replace($invsuff, '', $prev_data['number']));
			if ($prev_inv_number > 0) {
				$nextinvnum = $prev_inv_number;
			}
		}
	}
	//
	?>
	<div class="vbo-info-overlay-block">
		<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
		<div class="vbo-info-overlay-content vbo-info-overlay-content-invoices">
		<?php
		if (count($cid) == 1) {
		?>
			<div style="float: right;"><a href="index.php?option=com_vikbooking&task=editbusy&cid[]=<?php echo $cid[0]; ?>&frominv=1" class="btn btn-primary"><?php echo JText::translate('VBINVEDITBINFO'); ?></a></div>
		<?php
		}
		?>
			<h4><?php echo JText::translate('VBOGENINVOICES') ?> (<?php echo count($cid); ?>)</h4>
			<form action="index.php?option=com_vikbooking" method="post" id="vbo-geninv-form">
				<div class="vbo-calendar-cfield-entry">
					<label for="invoice_num"><?php echo JText::translate('VBINVSTARTNUM'); ?></label>
					<span><input type="number" min="1" size="4" value="<?php echo $nextinvnum; ?>" id="invoice_num" name="invoice_num" /></span>
				</div>
				<div class="vbo-calendar-cfield-entry">
					<label for="invoice_suff"><?php echo JText::translate('VBINVNUMSUFFIX'); ?></label>
					<span><input type="text" size="7" value="<?php echo $invsuff; ?>" id="invoice_suff" name="invoice_suff" /></span>
				</div>
				<div class="vbo-calendar-cfield-entry">
					<label for="invoice_date"><?php echo JText::translate('VBINVUSEDATE'); ?></label>
					<span>
						<select id="invoice_date" name="invoice_date">
						<?php
						if (!empty($oldinvdate)) {
							?>
							<option value="<?php echo date($df, $oldinvdate); ?>"><?php echo date($df, $oldinvdate); ?></option>
							<?php
						}
						?>
							<option value="<?php echo date($df, time()); ?>"><?php echo date($df, time()); ?></option>
							<option value="0"><?php echo JText::translate('VBINVUSEDATEBOOKING'); ?></option>
						</select>
					</span>
				</div>
				<div class="vbo-calendar-cfield-entry">
					<label for="company_info"><?php echo JText::translate('VBINVCOMPANYINFO'); ?></label>
					<span><textarea name="company_info" id="company_info" style="width: 98%; min-width: 98%; max-width: 98%; height: 70px;"><?php echo $companyinfo; ?></textarea></span>
				</div>
				<div class="vbo-calendar-cfield-entry">
					<label for="invoice_send"><i class="vboicn-envelop"></i><?php echo JText::translate('VBINVSENDVIAMAIL'); ?></label>
					<span><select id="invoice_send" name="invoice_send"><option value=""><?php echo JText::translate('VBNO'); ?></option><option value="1"><?php echo JText::translate('VBYES'); ?></option></select></span>
				</div>
				<br clear="all" />
				<div class="vbo-calendar-cfields-bottom">
					<button type="submit" class="btn btn-success"><i class="vboicn-file-text2"></i><?php echo JText::translate('VBOGENINVOICES'); ?></button>
				</div>
			<?php
			foreach ($cid as $invid) {
				echo '<input type="hidden" name="cid[]" value="'.$invid.'" />';
			}
			if ($pconfirmgen > 0) {
				echo '<input type="hidden" name="confirmgen" value="'.$cid[0].'" />';
			}
			?>
				<input type="hidden" name="option" value="com_vikbooking" />
				<input type="hidden" name="task" value="geninvoices" />
			</form>
		</div>
	</div>
	<script type="text/javascript">
	var vbo_overlay_on = false;
	jQuery(document).ready(function() {
		jQuery(".vbo-info-overlay-block").fadeIn(400, function() {
			if (jQuery(".vbo-info-overlay-block").is(":visible")) {
				vbo_overlay_on = true;
			} else {
				vbo_overlay_on = false;
			}
			<?php
			if ($pconfirmgen > 0) {
				?>
			if (confirm('<?php echo addslashes(JText::translate('VBCONFIRMGENINV')); ?>')) {
				document.getElementById('vbo-geninv-form').submit();
			}
				<?php
			}
			?>
		});
		jQuery(document).mouseup(function(e) {
			if (!vbo_overlay_on) {
				return false;
			}
			var vbo_overlay_cont = jQuery(".vbo-info-overlay-content");
			if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
				jQuery(".vbo-info-overlay-block").fadeOut();
				vbo_overlay_on = false;
			}
		});
		jQuery(document).keyup(function(e) {
			if (e.keyCode == 27 && vbo_overlay_on) {
				jQuery(".vbo-info-overlay-block").fadeOut();
				vbo_overlay_on = false;
			}
		});
	});
	</script>
	<?php
}