<?php
  /*
    Plugin Name: Draw Comments
    Plugin URI: http://www.azettl.de/2008/11/draw-comments/
    Description: This plugin allows your visitors to draw an image as extra comment.
    Version: 0.0.4
    Author: Andreas Zettl
    Author URI: http://azettl.de/
    Min WP Version: 2.6.2
    Max WP Version: 2.6.3
  */
  
  add_action('admin_menu', 'draw_add_menu');
	add_action('comment_form', 'comment_form');
  add_action('preprocess_comment', 'add_image',1);
  add_filter('comment_text', 'replace_image');

  $draw_do_action = get_option('draw_do_action');
  if ('insert' == $HTTP_POST_VARS['action']){
    update_option("draw_do_action",$HTTP_POST_VARS['draw_do_action']);
  }
  
  function draw_option_page() {
    echo '<div class="wrap">
            <h2>Draw Comments</h2>
            <form name="form1" method="post" action="'.$location.'">
              <label for="draw_do_action">Use "do_action(\'comment_form\', $post->ID);" to embed the drawing area:</label><br />
              <select name="draw_do_action">
                <option value="1" '.((get_option("draw_do_action") == '1') ? 'selected="selected"' : '').'>Yes</option>
                <option value="-1" '.((get_option("draw_do_action") == '-1') ? 'selected="selected"' : '').'>No</option>
              </select>
              <p>(If "No" selected, you have to use "echo getDrawArea().getColors();" instead of do_action)</p>
              <br /><br />
              <input type="submit" value="Save" />
              <input name="action" value="insert" type="hidden" />
            </form>
          </div>';
  }
  
  function draw_add_menu() {
    add_option("draw_do_action","");
    add_options_page('Draw Comments', 'Draw Comments', 9, __FILE__, 'draw_option_page');
  }

  function getDrawArea(){
    $rows = 40;
    $cols = 60;
    
    $ret = '<input type="hidden" id="draw_state" />';
    $ret .= '<input type="hidden" id="draw_color" value="#000000" />';
    $ret .= '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;" onmousedown="document.getElementById(\'draw_state\').value=1;" onmouseup="document.getElementById(\'draw_state\').value=0;">';
    for($i = 0; $i < $rows; $i++){
      $ret .= '<tr>';
      for($j = 0; $j < $cols; $j++){
        if(!empty($_POST['cell-'.$i.'-'.$j.''])){
          $ret .= '<td style="width:8px;height:8px;line-height:8px;padding:0px;margin:0px;border:1px solid grey;" bgcolor="'.$_POST['cell-'.$i.'-'.$j.''].'" onmouseover="if(document.getElementById(\'draw_state\').value == 1){ this.bgColor=document.getElementById(\'draw_color\').value; document.getElementById(\'cell-'.$i.'-'.$j.'\').value = document.getElementById(\'draw_color\').value }">';
          $ret .= '<input type="hidden" name="cell-'.$i.'-'.$j.'" id="cell-'.$i.'-'.$j.'" value="'.$_POST['cell-'.$i.'-'.$j.''].'"/>';
          $ret .= '</td>';
        }else{
          $ret .= '<td style="width:8px;height:8px;line-height:8px;padding:0px;margin:0px;border:1px solid grey;" onmouseover="if(document.getElementById(\'draw_state\').value == 1){ this.bgColor=document.getElementById(\'draw_color\').value; document.getElementById(\'cell-'.$i.'-'.$j.'\').value = document.getElementById(\'draw_color\').value }">';
          $ret .= '<input type="hidden" name="cell-'.$i.'-'.$j.'" id="cell-'.$i.'-'.$j.'" />';
          $ret .= '</td>';
        }
      }
      $ret .= '</tr>';
    }
    $ret .= '</table>';
    return $ret;
  }
  
  function getColors(){
    $ret = '<table cellpadding="4" cellspacing="4">';
    $ret .= '<tr>';
    $ret .= '<td>Choose Color: </td>';
    $ret .= '<td style="height:20px;width:20px;background-color:#FF0000;border:2px solid grey;" onclick="document.getElementById(\'draw_color\').value=\'#FF0000\';">&nbsp;</td>';
    $ret .= '<td style="height:20px;width:20px;background-color:#0000FF;border:2px solid grey;" onclick="document.getElementById(\'draw_color\').value=\'#0000FF\';">&nbsp;</td>';
    $ret .= '<td style="height:20px;width:20px;background-color:#FFFF00;border:2px solid grey;" onclick="document.getElementById(\'draw_color\').value=\'#FFFF00\';">&nbsp;</td>';
    $ret .= '<td style="height:20px;width:20px;background-color:#00FF00;border:2px solid grey;" onclick="document.getElementById(\'draw_color\').value=\'#00FF00\';">&nbsp;</td>';
    $ret .= '<td style="height:20px;width:20px;background-color:#000000;border:2px solid grey;" onclick="document.getElementById(\'draw_color\').value=\'#000000\';">&nbsp;</td>';
    $ret .= '<td style="height:20px;width:20px;background-color:#FFFFFF;border:2px solid grey;" onclick="document.getElementById(\'draw_color\').value=\'#FFFFFF\';">&nbsp;</td>';
    $ret .= '</tr>';
    $ret .= '</table>';
    return $ret;
  }
  
  function createImage(){
    $im = @ImageCreate (240, 160)
          or die ("Kann keinen neuen GD-Bild-Stream erzeugen");
    $background_color = ImageColorAllocate ($im, 255, 255, 255);
    
    
    $color_FF0000 = ImageColorAllocate ($im, 255, 0, 0);
    $color_0000FF = ImageColorAllocate ($im, 0, 0, 255);
    $color_FFFF00 = ImageColorAllocate ($im, 255, 255, 0);
    $color_00FF00 = ImageColorAllocate ($im, 0, 255, 0);
    $color_000000 = ImageColorAllocate ($im, 0, 0, 0);
    $color_FFFFFF = ImageColorAllocate ($im, 255, 255, 255);
    
    $drawn = 0;
    foreach($_POST as $pos => $color){
      if(!eregi('cell-', $pos)) continue;
      $position = explode("-", $pos);
      $changedpos1 = $position['1'] * 4;
      $changedpos2 = $position['2'] * 4;
      if(!empty($color)){
        switch($color){
          case '#FF0000':
            $imgcolor = $color_FF0000;
            $drawn = 1;
          	break;
          case '#0000FF':
            $imgcolor = $color_0000FF;
            $drawn = 1;
          	break;
          case '#FFFF00':
            $imgcolor = $color_FFFF00;
            $drawn = 1;
          	break;
          case '#00FF00':
            $imgcolor = $color_00FF00;
            $drawn = 1;
          	break;
          case '#000000':
            $imgcolor = $color_000000;
            $drawn = 1;
          	break;
          case '#FFFFFF':
            $imgcolor = $color_FFFFFF;
          	break;
        }
        imagefilledrectangle ($im, $changedpos2, $changedpos1, ($changedpos2+4), ($changedpos1+4), $imgcolor);
      }
    }
    
    if($drawn == 0) return false;
    
    @mkdir('wp-content/uploads/comments/', 0755);
    @mkdir('wp-content/uploads/comments/'.date('Y'), 0755);
    @mkdir('wp-content/uploads/comments/'.date('Y').'/'.date('m'), 0755);
    $image = 'wp-content/uploads/comments/'.date('Y').'/'.date('m').'/'.md5(uniqid(rand(), true)).'.jpg';
    ImagePNG ($im, $image);
    return $image;
  }
  
	function comment_form() {
		if(get_option('draw_do_action') != '-1'){
      echo getDrawArea().getColors();
    }
	}
	
	function add_image($comment){
	  $image = createImage();
	  if($image === false) return $comment;
	  $comment['comment_content'] .= '['.date('Y').'|'.date('m').'|'.str_replace('.jpg','', basename($image)).']';
    return $comment;
	}
	
	function replace_image($comment){
	   preg_match("/\[(.*)\]/", $comment, $result);
	   preg_match("/(.*)\|(.*)\|(.*)/", $result['1'], $splitresult);
	   if(is_file('wp-content/uploads/comments/'.$splitresult['1'].'/'.$splitresult['2'].'/'.$splitresult['3'].'.jpg')){
      $image = '<br/><img src="'.get_option('siteurl').'/wp-content/uploads/comments/'.$splitresult['1'].'/'.$splitresult['2'].'/'.$splitresult['3'].'.jpg" />';
      $comment = str_replace($result['0'], $image,$comment);
      echo $comment;
	   }else{
      echo $comment;
	   }
	}
?>
