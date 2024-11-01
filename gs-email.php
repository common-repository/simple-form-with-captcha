<?php
/*
Plugin Name: Simple form with captcha
Plugin URI: http://gsconsulting2010.com/wordpress-plugin-email-gsconsulting2010.php
Description: Used to view people who sign up and view/download email list.: SIMPLY enter  "__gs_mail_prog__ "  in the page you want  the form to display.
Version: 1.0
Author: G & S Consulting 2010
Author URI: http://GSConsulting2010.com
*/
?>
<?php
/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
	// boiler plate defs from WP site
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );


add_action('admin_menu', 'gs_email_add_pages');
add_action('the_content','gs_email_write_form');
add_action('get_header','do_export');



function gs_email_add_pages(){
  add_options_page('Manage Email', 'G&S Email Management', 'administrator', 'GS_EMAIL_ident', 'the_options_page');
  add_options_page('Manage Header', 'G&S Header Management', 'administrator', 'GS_EMAIL_ident1', 'the_header_page');

}


register_activation_hook(__FILE__, 'do_the_ddl');

// table creates

function do_the_ddl(){
   global $wpdb;

   if ($table_prefix == "")
     $table_prefix = $wpdb->prefix;


$member_table = $table_prefix . "gs_email_members";
$ddsql_mem = "CREATE TABLE if not exists " . $member_table . " (
  NMID int(10) unsigned NOT NULL AUTO_INCREMENT,
  FName varchar(50) NOT NULL DEFAULT '',
  LName varchar(50) NOT NULL DEFAULT '',
  email varchar(50) NOT NULL DEFAULT '',
  dte datetime NOT NULL,
  subscribe tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (NMID)
);";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($ddsql_mem);
//  $wpdb->query($ddsql_mem);

$other_table = $table_prefix . "gs_other";
$ddsql_oth = "CREATE TABLE if not exists " . $other_table . " (
  header varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (header)
);";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($ddsql_oth);
//  $wpdb->query($ddsql_oth);

}




// Writes and handles admin section page that
// allows user to put arbitrary text on top of the form.


function the_header_page(){

    global $wpdb;
    $message = "";

   if ($table_prefix == "")
     $table_prefix = $wpdb->prefix;

   $other_table = $table_prefix . "gs_other";

  if (isset($_POST['submit_email_header'])){
    $hstr =  nl2br($_POST['ta1']);

    $qry = "select count(*) as cnt from $other_table";

    $num = $wpdb->get_row($qry, ARRAY_N);

        if ( !empty($wpdb->error) )
            wp_die($wpdb->error->get_error_message());

    if ($num[0] != 0)
      $qry = "update $other_table set header =\"$hstr\"";
    else
      $qry = "insert into $other_table values(\"$hstr\")";

	$query = $wpdb->prepare($qry);
	$wpdb->query( $qry);

        if ( !empty($wpdb->error) )
            wp_die($wpdb->error->get_error_message());

            $message = "<p style = 'color: blue; font-weight: bold; font-size: 1.1em;'>You successfully submitted the header information</p>";

  }

  $qry = "select header from $other_table";


    $val = $wpdb->get_row($qry, ARRAY_N);

        if ( !empty($wpdb->error) )
            wp_die($wpdb->error->get_error_message());
 
    $taval =  $val[0];


  echo '<div style="text-align:center; color:green;">';
  echo '<h1>Email Signup Header Creation Page</h1>';
  echo '<h2>Type the text you want to appear on top of the signup form</h2>';
  echo '</div><br />';


$str = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);

echo "<form id='form' action='$str' method='post'>";
   echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;";
   echo "<textarea name='ta1' id='ta1' cols='80'>";
   echo str_replace("<br />", "", $taval);
   echo "</textarea>";

   echo "<br /><br />";

echo "<input id='submit' name='submit_email_header' type='submit' value='Update' />";
echo "</form>";

echo $message;

}



// Writes and handles admin page that shows the most recent
// 50 to sign up and also handles the download of the csv file.

function the_options_page(){
  echo '<div style="text-align:center; color:green;">';
  echo '<h1>Email Signup View and Export Page</h1>';
  echo '<h2>(The most recent are on top)</h2>';
  echo '</div>';

	global $wpdb;

   if ($table_prefix == "")
     $table_prefix = $wpdb->prefix;

   $member_table = $table_prefix . "gs_email_members";

   $qry = "select count(*) as cnt from $member_table";


    $num = $wpdb->get_row($qry, ARRAY_N);

        if ( !empty($wpdb->error) )
            wp_die($wpdb->error->get_error_message());
 
	// if section shows when there is at least
	// one signup. Start with the download link.
    if ( $num[0] != 0){




?>
<a href="<?php echo get_bloginfo('url'); ?>?do_export"><?php _e("Export list as a CSV file","gs-email"); ?></a>
<?php



	$sql = "select * from $member_table order by NMID desc limit 50";

echo "<div style=\"width: 500px;\">";
   echo "<table>";
   echo "<tr><th>First Name</th><th>Last Name</th><th>Email Address</th></tr>";
	foreach ($wpdb->get_results($sql) as $row) {
		print "<tr><td>". stripslashes($row->FName) . "</td><td>" . stripslashes($row->LName) . "</td><td>" . stripslashes($row->email) . "</tr>";
		}
   echo "</table>";
echo "</div>";

   }
   else{	// No signups? Just output string.
     echo "<br /><br />Nobody in the database yet.";
   }

}

// This function actually process the form. Does
// validation and updates DB. This is also where we
// check for the magic string and, if found, calls
// the function that really outputs the form.

function gs_email_write_form($content){
   global $wpdb;

   if (isset($_POST['submit_gs_email'])){
	$LName = "";
	$FName = "";
	$email = "";


$required_fields = array('FName'=>'First Name','LName'=>'Last Name','email'=>'email', 'dupemail'=>'email address already registered', 'valid_email'=>'Invalid email address', 'captcha'=>'Incorrect Image Code', 'dberror' => 'Database communication error');

	  $errors = array();

		// securimage class to check captcha
	$capstr = WP_PLUGIN_DIR . "/simple-form-with-captcha/securimage.gs/securimage.php";

      	require_once($capstr);
	$SecImg =  new securimage();

	$valid = $SecImg->check($_POST['captcha_code']);
		// get values from form
	$LName = trim($_POST['LName']);
	$FName = trim($_POST['FName']);
	$email = trim($_POST['email']);
		// error checks
       if ($FName == "")
	$errors[] = $required_fields['FName'];

       if ($LName == "")
	$errors[] = $required_fields['LName'];

       if ($email == "")
	$errors[] = $required_fields['email'];
		
		
	$EmNoDup =false;	

	    $ret = checkMail($email);

		if($ret == true){
		   $EmNoDup =true;	
		} 	
		else {
		
		$errors[] = $required_fields['dupemail'];
		
		}

		if(is_email($email,$check_dns = true)) {

		} 
		else {
		   $errors[] = $required_fields['valid_email'];
		}
		
	if(!$valid){
	   $errors[] = $required_fields['captcha'];	
	}

	if(empty($errors) ){    
		
		ob_start();
   global $wpdb;
   if ($table_prefix == "")
     $table_prefix = $wpdb->prefix;

   $member_table = $table_prefix . "gs_email_members";


	
$query = "insert into $member_table (LName, FName, email, dte, subscribe) VALUES ('{$LName}','{$FName}','{$email}', now(), '1')";

   $query = $wpdb->prepare($query);

	$wpdb->query( $query);


	echo "<h2>Thank you for signing up.<h2>";
	return;
	}  
		

		

   }


echo "<p align = 'center'>";
	
 if (!empty($errors)) {
					
   echo "<table width='80%' border='1' cellspacing='2' cellpadding='2'>
   <tr><th bgcolor='#FFFFAA' scope='col'>
   Please review the following fields:";
			foreach($errors as $error1) {
				echo " - " . $error1;
				
 			}			
 } 
 
echo  "</th></tr></table><br/>";
	
		echo "</p>";
	




   if (preg_match("/\_\_gs_mail_prog\_\_/", $content)){
     return preg_replace('/\_\_gs_mail_prog\_\_/',write_the_form($FName, $LName, $email),$content);
   }
   else{
     return $content;
   }
}



// Function that writes the actual form with any
// header text the user added.

function write_the_form($FName, $LName, $email){
   global $wpdb;

   if ($table_prefix == "")
     $table_prefix = $wpdb->prefix;

  $other_table = $table_prefix . "gs_other";

  $qry = "select header from $other_table";

    $num = $wpdb->get_row($qry, ARRAY_N);

        if ( !empty($wpdb->error) )
            wp_die($wpdb->error->get_error_message());
 

    $head_val =  $num[0];


  echo "<p style='font-size:1.3em; color: #003333;'><strong>" . $head_val . "</strong><br /><br /></p>";



$str = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);

echo "<table border = '1'cellpadding = '15' cellspacing = '5'><tr><td>";

echo "<table border = '0'cellpadding = '5' cellspacing = '5'>";

echo "<form id='form' action='$str' method='post'>";

echo "<tr><td><strong>&#42;First Name </strong><span><input id='FName'style = 'background: #FFFFCC;' maxlength='45' name='FName' value='$FName' size='45' type='text' /></span></td></tr>";

echo "<tr><td><span><strong>&#42;Last Name  &nbsp;</strong>";
echo "<input id='LName' maxlength='45' name='LName' value='$LName'style = 'background: #FFFFCC;' size='45' type='text' /></span></td></tr>";

echo "<tr><td><span><strong>&#42;Email &nbsp;</strong><input id='email'style = 'background: #FFFFCC;' class='stylereq' maxlength='100' name='email' value='$email' size='51' type='text' /></span></td></tr>";


$capstr = WP_PLUGIN_URL . "/simple-form-with-captcha/securimage.gs/securimage_show.php";


echo "<tr><td style = 'text-align: center;'><img style='border:1;' id='captcha' name='captcha' src='" . $capstr . "' alt='CAPTCHA Image' /> <br />";
echo "<label for captcha_code>Enter code below</label></td></tr>";

echo "<tr><td style = 'text-align: center;'><input type='text'style = 'background: #FFFFCC;' name='captcha_code' size='10' maxlength='6' />";
echo "</td></tr>";

echo "<tr><td style = 'text-align: center;'><input id='submit' name='submit_gs_email' type='submit' value='Sign Up' />";

echo "</td></tr></table>";

echo "</td></tr></table>";

echo "</form>";

}


// Creates and exports list of people who signed up.

function do_export(){
 if (isset($_GET['do_export'])){
  
   global $wpdb;

   if ($table_prefix == "")
     $table_prefix = $wpdb->prefix;

   $mem_table = $table_prefix . "gs_email_members";

   $sql = "SELECT FName,LName,email from $mem_table WHERE subscribe='1'  ORDER BY NMID DESC";

   $file = "gsemail-";
   $file .= date("m-d-y");
   $file .= ".csv";


   $handle = fopen($file, "w");

   if ($handle){

     foreach ($wpdb->get_results($sql) as $row) {

       $str = "\"" . stripslashes($row->FName) . "\",";
       $str .= "\"" . stripslashes($row->LName) . "\",";
       $str .= "\"" . stripslashes($row->email) . "\"";
       $str .= "\r\n";

       fwrite($handle, $str);
     }
   }
   else{
     echo "Could not open file.";
   }


   if ($handle){
    header("Content-type: application/force-download");
    header("Content-Transfer-Encoding: Binary");
    header("Content-length: ".filesize($file));
    header("Content-disposition: attachment; filename=\"".basename($file)."\"");
    readfile("$file");


     fclose($handle);
     unlink($file);
   }


exit;

  }
  else
    return;
}

	// Checks for email dupes in local DB.
	// Returns:
	//   true if they are not stored already
	//   false if we already have them
function checkMail($eadr){
   global $wpdb;

   if ($table_prefix == "")
     $table_prefix = $wpdb->prefix;

   $mem_table = $table_prefix . "gs_email_members";

   $qry = "select count(*) as cnt from $mem_table where email='$eadr'";


    $num = $wpdb->get_row($qry, ARRAY_N);

        if ( !empty($wpdb->error) )
            wp_die($wpdb->error->get_error_message());
 

    if ( $num[0] == 0)
      return true;
    else
      return false;



}
?>
