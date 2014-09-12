<?php
/*
Plugin Name:  Direct Filesystem
Plugin URI: http://www.infinitewp.com
Description: Read and Write File Using Direct File System
Version: 1.0
Author: Naga 
Author URI: http://www.revmakx.com/
License: The MIT License (MIT)

Copyright (c) 2014 nagarevmakx

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/


    

/**
 * Direct File Sytsem(under Plugins menu)
 * 
 **/
 add_action('admin_menu', 'filesystem_direct');
 
 function filesystem_direct() {
    add_submenu_page( 'plugins.php', 'Filesystem direct demo page', 'Filesystem Direct', 'upload_files', 'filesystem_demo_direct', 'filesystem_screen_direct' );
}

function filesystem_screen_direct() {

$url = "plugins.php?page=filesystem_demo_direct";
$output = $error = '';


if(isset($_POST['texttofile'])){//new submission
    
    if(false === ($output = filesystem_text_write_file($url))){
        return; //we are displaying credentials form - no need for further processing
    
    } elseif(is_wp_error($output)){
        $error = $output->get_error_message();
        $output = '';
    }
    
} else {//read from file
    
    if(false === ($output = filesystem_text_read_file($url))){
        return; //we are displaying credentials form no need for further processing
    
    } elseif(is_wp_error($output)) {
        $error = $output->get_error_message();
        $output = '';
    }
}
$output = esc_textarea($output); //escaping for printing

?>
    
<div class="wrap">

<h2>Filesystem Direct </h2>

<?php if(!empty($error)): ?>
    <div class="error below-h2"><?php echo $error;?></div>
<?php endif; ?>

<form method="post" action="" style="margin-top: 3em;">

<?php wp_nonce_field('filesystem_demo_screen'); ?>

<fieldset class="form-table">
    <label for="texttofile">Type Text Here</label><br>
    <textarea id="texttofile" name="texttofile" rows="8" class="large-text"><?php echo $output;?></textarea>
</fieldset>
    
   
<?php submit_button('Submit', 'primary', 'texttofile_submit', true);?>

</form>
</div>
<?php
}

/**
 * Initialize Filesystem object
 *
 * @param str $url - URL of the page
 * @param str $method - connection method ,am using Direct method
 * @param str $context - destination folder (where is file located)
 * @param array $fields - fileds of $_POST array that should be preserved between screens

 **/
function filesystem_init($url, $method, $context, $fields = null) {
    global $wp_filesystem;
    
    /* first attempt to get credentials */
    if (false === ($creds = request_filesystem_credentials($url, $method, false, $context, $fields))) {
        return false;
    }
     
    if (!WP_Filesystem($creds)) {
        request_filesystem_credentials($url, $method, true, $context);
        return false;
    }
    
    return true; //filesystem object successfully initiated
}


/**
 * writing into file
 *
 * @param str $url - URL of the page

 **/
function filesystem_text_write_file($url){
    global $wp_filesystem;
    
    check_admin_referer('filesystem_demo_screen');
    
    $texttofile = sanitize_text_field($_POST['texttofile']); 
    $form_fields = array('texttofile'); 
    $method = ''; //leave this empty to perform test for 'direct' writing
    $context = WP_PLUGIN_DIR . '/filesystem'; //target folder
            
    $url = wp_nonce_url($url, 'filesystem_demo_screen'); 
    
    if(!filesystem_init($url, $method, $context, $form_fields))
        return false; //stop further processign when request form is displaying
    
  
    $target_dir = $wp_filesystem->find_folder($context);
    $target_file = trailingslashit($target_dir).'test.txt';
       
    
    /* write into file */
    if(!$wp_filesystem->put_contents($target_file, $texttofile, FS_CHMOD_FILE)) 
        return new WP_Error('writing_error', 'Error when writing file'); 
    return $texttofile;
}

function filesystem_text_read_file($url){
    global $wp_filesystem;
    $texttofile = '';
    
    $url = wp_nonce_url($url, 'filesystem_demo_screen');
    $method = ''; //leave this empty to perform test for 'direct' writing
    $context = WP_PLUGIN_DIR . '/filesystem'; //target folder   
    
    if(!filesystem_init($url, $method, $context))
        return false; 
   
    $target_dir = $wp_filesystem->find_folder($context);
    $target_file = trailingslashit($target_dir).'test.txt';
        
    /* read the file */
    if($wp_filesystem->exists($target_file)){ //check for existence
        
        $texttofile = $wp_filesystem->get_contents($target_file);
        if(!$texttofile)
            return new WP_Error('reading_error', 'file is empty'); 
    }   
    
    return $texttofile;    
}

?>