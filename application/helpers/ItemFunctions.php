<?php
/**
 * All Item helper functions
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka_ThemeHelpers
 * @subpackage ItemHelpers
 **/
 
 /**
  * @since 0.10
  * @uses current_user_tags()
  * @uses get_current_item()
  * @param Item|null $item Check for this specific item record (current item if null).
  * @return array
  **/
 function current_user_tags_for_item($item=null)
 {
     if (!$item) {
         $item = get_current_item();
     }
     // eventually, we need to not use current_user_tags because it is deprecated
     return current_user_tags($item);
 }
 
 /**
  * @since 0.10
  * @uses display_files()
  * @uses get_current_item()
  * @param array $options 
  * @param array $wrapperAttributes
  * @param Item|null $item Check for this specific item record (current item if null).
  * @return string HTML
  **/
 function display_files_for_item($options = array(), $wrapperAttributes = array('class'=>'item-file'), $item = null)
 {
     if(!$item) {
         $item = get_current_item();
     }

     return display_files($item->Files, $options, $wrapperAttributes);
 }
 
 /**
  * Returns the HTML markup for displaying a random featured item.  Most commonly
  * used on the home page of public themes.
  * 
  * @since 0.10
  * @param boolean $withImage Whether or not the featured item should have an image associated 
  * with it.  If set to true, this will either display a clickable square thumbnail 
  * for an item, or it will display "You have no featured items." if there are 
  * none with images.
  * @return string HTML
  **/
 function display_random_featured_item($withImage=false)
 {
    $featuredItem = random_featured_item($withImage);
 	$html = '<h2>Featured Item</h2>';
 	if ($featuredItem) {
 	    $itemTitle = item('Dublin Core', 'Title', array(), $featuredItem);
        
 	   $html .= '<h3>' . link_to_item($itemTitle, array(), 'show', $featuredItem) . '</h3>';
 	   if (item_has_thumbnail($featuredItem)) {
 	       $html .= link_to_item(item_square_thumbnail(array(), 0, $featuredItem), array('class'=>'image'), 'show', $featuredItem);
 	   }
 	   // Grab the 1st Dublin Core description field (first 150 characters)
 	   if ($itemDescription = item('Dublin Core', 'Description', array('snippet'=>150), $featuredItem)) {
 	       $html .= '<p class="item-description">' . $itemDescription . '</p>';
       }
 	} else {
 	   $html .= '<p>No featured items are available.</p>';
 	}

     return $html;
 }
 
 /**
  * Retrieve the current Item record.
  * 
  * @since 0.10
  * @throws Exception
  * @return Item
  **/
 function get_current_item()
 {
     if (!($item = __v()->item)) {
         throw new Exception('An item has not been set to be displayed on this theme page!  Please see Omeka documentation for details.');
     }

     return $item;
 }
 
 /**
  * Retrieve an Item object directly by its ID.
  * 
  * Example of usage on a public theme page:
  * 
  * $item = get_item_by_id(4);
  * set_current_item($item); // necessary to use item() and other similar theme API calls.
  * echo item('Dublin Core', 'Title');
  * 
  * @since 0.10
  * @param integer $itemId
  * @return Item|null
  **/
 function get_item_by_id($itemId)
 {
     return get_db()->getTable('Item')->find($itemId);
 }
 
 /**
  * Retrieve a set of Item records corresponding to the criteria given by $params.
  * 
  * This could be used on the public theme like so:
  * 
  * set_items_for_loop(get_items('tags'=>'foo, bar', 'recent'=>true), 10);
  * while (loop_items()): ....
  * 
  * @since 0.10
  * @see ItemTable::applySearchFilters()
  * @param array $params
  * @param integer $limit The maximum number of items to return.
  * @return array
  **/
 function get_items($params = array(), $limit = 10)
 {
     return get_db()->getTable('Item')->findBy($params, $limit);
 }
 
 /**
  * Retrieve the set of items for the current loop.
  * 
  * @since 0.10
  * @return array
  **/
 function get_items_for_loop()
 {
     return __v()->items;
 }
 
 /**
  * Retrieve the next item in the database.  
  * 
  * @todo Should this look for the next item in the loop, or just via the database?
  * @since 0.10
  * @param Item|null Check for this specific item record (current item if null).
  * @return Item|null
  **/
 function get_next_item($item=null)
 {
     if (!$item) {
         $item = get_current_item();
     }
     return $item->next();
 }

 /**
  * @see get_previous_item()
  * @since 0.10
  * @param Item|null Check for this specific item record (current item if null).
  * @return Item|null
  **/
 function get_previous_item($item=null)
 {
     if (!$item) {
         $item = get_current_item();
     }
     return $item->previous();
 }
  
 /**
  * Determine whether or not there are any items in the database.
  * 
  * @since 0.10
  * @return boolean
  **/
 function has_items()
 {
     return (total_items() > 0);    
 }

 /**
  * @since 0.10
  * @return boolean
  */
 function has_items_for_loop()
 {
     $view = __v();
     return ($view->items and count($view->items));
 }
 
 /**
  * Retrieve the values for a given field in the current item.
  * 
  * @since 0.10
  * @uses Omeka_View_Helper_RecordMetadata::_get() Contains instructions and 
  * examples.
  * @uses Omeka_View_Helper_ItemMetadata::_getRecordMetadata() Contains a list of
  * all fields that do not belong to element sets, e.g. 'id', 'date modified', etc.
  * @param string $elementSetName
  * @param string $elementName
  * @param array $options
  * @param Item|null Check for this specific item record (current item if null).
  * @return string|array|null
  **/
 function item($elementSetName, $elementName = null, $options = array(), $item = null)
 {
     if (!$item) {
         $item = get_current_item();
     }
     return __v()->itemMetadata($item, $elementSetName, $elementName, $options);
 }

 /**
  * Determine whether or not the current item belongs to a collection.
  * 
  * @since 0.10
  * @param string|null The name of the collection that the item would belong
  * to.  If null, then this will check to see whether the item belongs to
  * any collection.
  * @param Item|null Check for this specific item record (current item if null).
  * @return boolean
  **/
 function item_belongs_to_collection($name=null, $item=null)
 {
     //Dependency injection
     if(!$item) {
         $item = get_current_item();
     }
     
     return (!empty($item->collection_id) and (!$name or $item->Collection->name == $name) and ($item->Collection->public or has_permission('Collections', 'showNotPublic')));
 }

 /**
  * Retrieve a valid citation for the current item.
  *
  * Generally follows Chicago Manual of Style note format for webpages.  Does not 
  * account for multiple creators or titles. 
  *
  * @since  0.10
  * @return string
  **/
 function item_citation()
 {
     $creator    = strip_formatting(item('Dublin Core', 'Creator'));
     $title      = strip_formatting(item('Dublin Core', 'Title'));
     $siteTitle  = strip_formatting(settings('site_title'));
     $itemId     = item('id');
     $accessDate = date('F j, Y');
     $uri        = html_escape(abs_uri());

     $cite = '';
     if ($creator) {
         $cite .= "$creator, ";
     }
     if ($title) {
         $cite .= "\"$title,\" ";
     }
     if ($siteTitle) {
         $cite .= "in $siteTitle, ";
     }
     $cite .= "Item #$itemId, ";
     $cite .= "$uri ";
     $cite .= "(accessed $accessDate).";

     return $cite;
 }
 
 /**
  * Determine whether or not a specific element uses HTML.  By default this will
  * test the first element text, though it is possible to test against a different
  * element text by modifying the $index parameter.
  * 
  * @since 0.10
  * @param string
  * @param string
  * @param integer
  * @param Item|null Check for this specific item record (current item if null).
  * @return boolean
  **/
 function item_field_uses_html($elementSetName, $elementName, $index=0, $item = null)
 {
     if (!$item) {
         $item = get_current_item();
     }

     $textRecords = $item->getElementTextsByElementNameAndSetName($elementName, $elementSetName);
     $textRecord = @$textRecords[$index];

     return ($textRecord instanceof ElementText and $textRecord->isHtml());
 }
 
 /**
  * @see item_thumbnail()
  * @since 0.10
  * @param array $props
  * @param integer $index
  * @return string HTML
  **/
 function item_fullsize($props = array(), $index = 0, $item = null)
 {
     return item_image('fullsize', $props, $index, $item);
 }
 
 /**
  * Determine whether or not the item has any files associated with it.
  * 
  * @since 0.10
  * @see has_files()
  * @uses Item::hasFiles()
  * @param Item|null Check for this specific item record (current item if null).
  * @return boolean
  **/
 function item_has_files($item=null)
 {
     if(!$item) {
         $item = get_current_item();
     }
     return $item->hasFiles();
 }
 
 /**
  * @since 0.10
  * @param Item|null Check for this specific item record (current item if null).
  * @return boolean
  **/
 function item_has_tags($item=null)
 {
     if(!$item) {
         $item = get_current_item();
     }
     return (count($item->Tags) > 0);
 }
 
 /**
  * Determine whether an item has an item type.  
  * 
  * If no $name is given, this will return true if the item has any item type 
  * (items do not have to have an item type).  If $name is given, then this will
  * determine if an item has a specific item type.
  * 
  * @since 0.10
  * @param string|null $name
  * @param Item|null Check for this specific item record (current item if null).
  * @return boolean
  **/
 function item_has_type($name = null, $item = null)
 {
     if(!$item) {
         $item = get_current_item();
     }

     $itemTypeName = item('Item Type Name');
     return ($name and ($itemTypeName == $name)) or (!$name and !empty($itemTypeName));
 }
 
 /**
  * Determine whether or not the item has a thumbnail image that it can display.
  * 
  * @since 0.10
  * @param Item|null Check for this specific item record (current item if null).
  * @return void
  **/
 function item_has_thumbnail($item=null)
 {
     if(!$item) {
         $item = get_current_item();
     }
     return $item->hasThumbnail();
 }
 
 /**
  * Primarily used internally by other theme helpers, not intended to be used 
  * within themes.  Plugin writers creating new helpers may want to use this 
  * function to display a customized derivative image.
  * 
  * @since 0.10
  * @param string $imageType
  * @param array $props
  * @param integer $index
  * @param Item|null Check for this specific item record (current item if null).
  * @return void
  **/
 function item_image($imageType, $props = array(), $index = 0, $item = null)
 {
     if (!$item) {
         $item = get_current_item();
     }
     
     $imageFile = get_db()->getTable('File')->findWithImages($item->id, $index);
     
     $width = @$props['width'];
     $height = @$props['height'];

     require_once 'Media.php';
     $media = new Omeka_View_Helper_Media;
     return $media->archive_image($imageFile, $props, $width, $height, $imageType); 
 }
 
 /**
  * Returns the HTML for an item search form
  *
  * @param array $props
  * @param string $formActionUri
  * @return string
  **/	
 function items_search_form($props=array(), $formActionUri = null) 
 {
     return __v()->partial('items/advanced-search.php', array('isPartial'=>true, 'formAttributes'=>$props, 'formActionUri'=>$formActionUri));
 }
 
 /**
  * @see item_thumbnail()
  * @since 0.10
  * @param array $props
  * @param integer $index
  * @param Item $item The item to which the image belongs
  * @return string HTML
  **/
 function item_square_thumbnail($props = array(), $index = 0, $item = null)
 {
     return item_image('square_thumbnail', $props, $index, $item);
 }

 /**
  * HTML for a thumbnail image associated with an item.  Default parameters will
  * use the first image, but that can be changed by modifying $index.
  * 
  * @since 0.10
  * @uses item_image()
  * @param array $props A set of attributes for the <img /> tag.
  * @param integer $index The position of the file to use (starting with 0 for 
  * the first file).
  * @param Item $item The item to which the image belongs  
  * @return string HTML
  **/
 function item_thumbnail($props = array(), $index = 0, $item = null)
 {
     return item_image('thumbnail', $props, $index, $item);
 }
 
 /**
  * Loops through items assigned to the view.
  * 
  * @since 0.10
  * @return mixed The current item
  */
 function loop_items()
 {
     return loop_records('items', get_items_for_loop());
 }
 
 /**
  * Loops through files assigned to the current item.
  * 
  * @since 0.10
  * @return mixed The current file within the loop.
  * @param Item|null Check for this specific item record (current item if null).
  */
 function loop_files_for_item($item=null)
 {
     if (!$item) {
         $item = get_current_item();
     }
     $files = $item->Files;
     return loop_records('files_for_item', $files);
 }
 
 /**
  * @since 0.10
  * @access private
  * @see loop_items()
  * @param Item
  * @return void
  **/
 function set_current_item(Item $item)
 {
     $view = __v();
     $view->previous_item = $view->item;
     $view->item = $item;
 }
 
 /**
  * @since 0.10
  * @param array $items
  */
 function set_items_for_loop($items)
 {
     $view = __v();
     $view->items = $items;
 }
 
 /**
  * Retrieve the set of all metadata for the current item.
  * 
  * @since 0.10
  * @uses Omeka_View_Helper_ItemMetadata
  * @param array $options Optional
  * @param Item|null Check for this specific item record (current item if null).
  * @return string|array
  **/
 function show_item_metadata(array $options = array(), $item=null)
 {
     if (!$item) {
         $item = get_current_item();
     }
     return __v()->itemMetadataList($item, $options);
 }
 
 /**
  * Returns the most recent items
  * 
  * @param integer $num The maximum number of recent items to return
  * @return array
  **/
 function recent_items($num = 10) 
 {
 	return get_db()->getTable('Item')->findBy(array('recent'=>true), $num);
 }

 /**
  * Returns a randome featured item
  * 
  * @since 7/3/08 This will retrieve featured items with or without images by
  *  default. The prior behavior was to retrieve only items with images by
  *  default.
  * @param string $hasImage
  * @return Item
  */
 function random_featured_item($hasImage=false) 
 {
 	return get_db()->getTable('Item')->findRandomFeatured($hasImage);
 }
 
 /**
  * Returns the total number of items
  *
  * @return integer
  **/
 function total_items() 
 {	
 	return get_db()->getTable('Item')->count();
 }