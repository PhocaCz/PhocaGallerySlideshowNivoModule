<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Module
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die('Restricted access');
if (!JComponentHelper::isEnabled('com_phocagallery', true)) {
	return JError::raiseError(JText::_('Phoca Gallery Error'), JText::_('Phoca Gallery is not installed on your system'));
}

if (! class_exists('PhocaGalleryLoader')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocagallery'.DS.'libraries'.DS.'loader.php');
}

phocagalleryimport('phocagallery.path.path');
phocagalleryimport('phocagallery.file.file');
phocagalleryimport('phocagallery.file.filethumbnail');
phocagalleryimport('phocagallery.text.text');

$db 		= &JFactory::getDBO();
$document	= JFactory::getDocument();


$module_css_style	= trim( $params->get( 'module_css_style' ) );
$catId 				= $params->get( 'category_id', 0 );
$count				= $params->get( 'count_images', 5 );
$width 				= $params->get( 'width', 970 );
$height				= $params->get( 'height', 230 );
$posBullets			= $params->get( 'pos_bullets', '-30');
$effect				= $params->get( 'effect', 'random');
$animSpeed			= $params->get( 'anim_speed', 500);
$pauseTime			= $params->get( 'pause_time', 3000);
$directionNav		= $params->get( 'direction_nav', 'true');
$directionNavHide	= $params->get( 'direction_nav_hide', 'true');
$controlNav			= $params->get( 'control_nav', 'true');
$pauseOnHover		= $params->get( 'pause_hover', 'true');
$keyboardNav		= $params->get( 'keyboard_nav', 'true');
$descOpacity		= $params->get( 'desc_opacity', '0.6');
$desc				= $params->get( 'display_desc', 1);

$pathImg 			= JURI::base(true).'/modules/mod_phocagallery_slideshow_nivo/images/';
$mode				= 'horizontal';


JHTML::stylesheet('modules/mod_phocagallery_slideshow_nivo/css/nivo-slider.css' );
JHTML::stylesheet('modules/mod_phocagallery_slideshow_nivo/css/style.css' );
//$document->addScript(JURI::base(true).'/modules/mod_phocagallery_slideshow_nivo/javascript/jquery-1.6.1.min.js');
$document->addScript(JURI::base(true).'/components/com_phocagallery/assets/jquery/jquery-1.6.4.min.js');
$document->addScript(JURI::base(true).'/modules/mod_phocagallery_slideshow_nivo/javascript/jquery.nivo.slider.js');

$catidSQL = '';
if ((int)$catId > 0) {
	$catidSQL = ' AND a.catid = ' . (int)$catId;
}

// IMAGES
$query      = ' SELECT a.id, a.title, a.description, a.filename, a.extl'
			. ' FROM #__phocagallery_categories AS cc'
			. ' LEFT JOIN #__phocagallery AS a ON a.catid = cc.id'
			. ' WHERE a.published = 1'
			. $catidSQL
			//. ' WHERE cc.published = 1 AND a.published = 1 AND a.catid = ' . (int)$catId
			//. ' ORDER BY RAND()'
			. ' ORDER BY a.ordering'
			. ' LIMIT '.(int)$count;
$db->setQuery($query);
$images = $db->loadObjectList();

?>


<?php
$oI = '';
$oH = '';
$i = count($images);
if ($i > 0) {
	foreach ($images as $k => $v) {
		
		$title 		= '';
		$titleId	= '';
		if ($v->description != '' && $desc == 1) {
			$title = 'title="#pgsnivohtmlc'.$v->id.'"';
			$titleId	= 'pgsnivohtmlc'.$v->id;
		}
		
		if (isset($v->extl) &&  $v->extl != '') {
			$oI .= '<img src="'.PhocaGalleryText::strTrimAll($v->extl).'" alt="'.htmlspecialchars($v->title).'" '.$title.' />'. "\n";
		} else {
			$thumbLink	= PhocaGalleryFileThumbnail::getThumbnailName($v->filename, 'large');
			$oI .= '<img src="'.JURI::base(true).'/'.$thumbLink->rel.'" alt="'.htmlspecialchars($v->title).'" '.$title.' />'. "\n";
		}
		
		if ($v->description != '' && $desc == 1) {	
			$oH .= '<div id="'.$titleId.'" class="nivo-html-caption">';
			$oH .= str_replace ('#000000', '#ffffff', $v->description);
			$oH .= '</div>'. "\n";	
		}
	}
	
	if ($posBullets != 0) {
		$left = ((int)$width - ($i * 22)) / 2;
		$css ='.nivo-controlNav {
			position:absolute;
			left:'.$left.'px;
			bottom:'.$posBullets.'px;
			}';
	} else {
		$css ='.nivo-controlNav {
			display:none;
			}';
	}
	
	$document->addCustomTag( "<style type=\"text/css\"> \n"  
				.strip_tags($css). "\n"			
				." </style> \n");
}
?>
<div id="pgsnivo-wrapper">
	<div id="pgsnivo-slider-wrapper">
        <div id="pgsnivo-slider" class="nivoSlider" style="width:<?php echo $width; ?>px;height: <?php echo $height; ?>px">
            <?php echo $oI; ?>
        </div>
        <?php echo $oH; ?>
    </div>
</div>
<?php 

$js = '
<script type="text/javascript">
var pgSNJQ = jQuery.noConflict();
    pgSNJQ(window).load(function() {
        pgSNJQ(\'#pgsnivo-slider\').nivoSlider({
		effect:\''.$effect.'\',
		animSpeed: '.(int)$animSpeed.',
		pauseTime: '.(int)$pauseTime.',
		directionNav: '.$directionNav.',
		directionNavHide: '.$directionNavHide.',
		controlNav: '.$controlNav.',
		keyboardNav:'.$keyboardNav.',
        pauseOnHover: '.$pauseOnHover.',
        manualAdvance: false,
        captionOpacity: '.(float)$descOpacity.',
        prevText: \''.JText::_('MOD_PHOCAGALLERY_SLIDESHOW_NIVO_PREV').'\',
        nextText: \''.JText::_('MOD_PHOCAGALLERY_SLIDESHOW_NIVO_NEXT').'\'
		});
    });
    </script>';

$document->addCustomTag($js);
?>