<?php
function pro_polls_create_custom_post_type($post_type_slug,$post_type_singular_name, $post_type_plural_name )
{
   $labels = array(
    "name" => "$post_type_plural_name",
    "singular_name" => "$post_type_singular_name",
    "add_new" => "Add New $post_type_singular_name",
    "add_new_item" => "Add New $post_type_singular_name",
    "edit_item" => "Edit $post_type_singular_name",
    "new_item" => "New $post_type_singular_name",
    "all_items" => "All $post_type_plural_name",
    "view_item" => "View $post_type_plural_name",
    "search_items" => "Search  $post_type_plural_name",
    "not_found" =>  "No $post_type_plural_name found",
    "not_found_in_trash" => "No $post_type_plural_name found in Trash", 
    "parent_item_colon" => "",
    "menu_name" => "Pro $post_type_plural_name",
	"attributes" =>"$post_type_plural_name Attributes",
  );
  
 

  $args = array(
    "labels" => $labels,
    "public" => false,
    "publicly_queryable" => false,
    "show_ui" => true, 
    "show_in_menu" => true, 
    "show_in_nav_menus" => false,
    "query_var" => true,
    "rewrite" => array( "slug" => "$post_type_slug" ),
    "capability_type" => "post",
    "has_archive" => true, 
    "menu_position" => null,
    //"supports" => array( "title", "editor", "author", "thumbnail", "excerpt", "comments", "page-attributes" )
    "supports" => array( "title", "page-attributes" )
  ); 
  

  register_post_type( "$post_type_slug", $args );
  add_filter( 'post_updated_messages', 'pro_polls_updated_messages' );
  
}
function pro_polls_updated_messages( $messages ) 
{
	$post = get_post();
	//$post_type        = get_post_type( $post );
	//$post_type_object = get_post_type_object( $post_type );

	$messages[PRO_POLL_POST_TYPE_SLUG] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Poll updated.', 'your-plugin-textdomain' ),
		2  => __( 'Custom field updated.', 'your-plugin-textdomain' ),
		3  => __( 'Custom field deleted.', 'your-plugin-textdomain' ),
		4  => __( 'Poll updated.', 'your-plugin-textdomain' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Book restored to revision from %s', 'your-plugin-textdomain' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Poll published.', 'your-plugin-textdomain' ),
		7  => __( 'Poll saved.', 'your-plugin-textdomain' ),
		8  => __( 'Poll submitted.', 'your-plugin-textdomain' ),
		9  => sprintf(
			__( 'Poll scheduled for: <strong>%1$s</strong>.', 'your-plugin-textdomain' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i', 'your-plugin-textdomain' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Poll draft updated.', 'your-plugin-textdomain' )
	);
 
	return $messages;
}

function pro_polls_add_admin_menu()
{
	 // Now add the submenu page for Help
    $menu_slug = 'edit.php?post_type='.PRO_POLL_POST_TYPE_SLUG;
    $submenu_page_title = 'Pro Polls Settings';
    $submenu_title = 'Pro Polls Settings';
    $submenu_slug = 'pro-polls-settings';
    $submenu_function = 'pro_polls_show_settings_page';
	
    $capability = 'manage_options';
   // add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);	 // Now add the submenu page for Help
	
    $menu_slug = 'edit.php?post_type='.PRO_POLL_POST_TYPE_SLUG;
    $submenu_page_title = 'Wpmqp Polls Questions';
    $submenu_title = '';
    $submenu_slug = 'pro-polls-questions';
    $submenu_function = 'pro_polls_show_questions_page'; 
	
    $capability = 'manage_options';
    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);	
	
    $menu_slug = 'edit.php?post_type='.PRO_POLL_POST_TYPE_SLUG;
    $submenu_page_title = 'Pro Polls Results';
    $submenu_title = '';
    $submenu_slug = 'pro-polls-results';
    $submenu_function = 'pro_polls_show_results_page'; 
	
    $capability = 'manage_options';
    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	
}

function pro_polls_add_links_meta_box()
{
	global $post;
	$screen = get_current_screen(); 
	//pro_polls_dump($screen);
	if($screen->post_type !=  PRO_POLL_POST_TYPE_SLUG)
		return;
	if($screen->action != "add") 
		add_meta_box('pro-polls-links-metabox-id', 'Links', 'pro_polls_show_links', PRO_POLL_POST_TYPE_SLUG, 'normal', 'high');
}



function pro_polls_show_links()
{ 
	global $post;?> 
<a href='edit.php?post_type=<?php echo PRO_POLL_POST_TYPE_SLUG; ?>&page=pro-polls-questions&poll_id=<?php echo $post->ID; ?>' class="button button-primary button-large">View Questions</a> 
<a href='edit.php?post_type=<?php echo PRO_POLL_POST_TYPE_SLUG; ?>&page=pro-polls-results&poll_id=<?php echo $post->ID; ?>'  class="button button-error button-large">View Submissions</a> 
<?php }
 

function pro_polls_init()
{
	 
	
	pro_polls_create_custom_post_type(PRO_POLL_POST_TYPE_SLUG,'Poll', 'Polls' );
	add_action('add_meta_boxes', 'pro_polls_add_links_meta_box'); 
	add_shortcode( 'pro_polls', 'pro_polls_show_with_shortcode' );
	
 }

function pro_polls_dump($var)
{
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
}



function pro_polls_install() {
   global $wpdb;
   global $pro_polls_db_version;

   $table_name = PRO_POLL_RESULT_TABLE;
      
   $sql = " CREATE TABLE ". $table_name."(
  ID int(11) NOT NULL AUTO_INCREMENT,
  poll_id int(11) NOT NULL,
  user_id bigint(20) NOT NULL,
  answers text NOT NULL,
  submitted_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status enum('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (ID),
  KEY poll_id (poll_id)
);";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
 
   add_option( "pro_polls_db_version", $pro_polls_db_version );
}



/*
function pro_polls_check_access($post_id, $user_id)
{
	$post_access = true;
	$required_capabilities = (array)get_post_meta($post_id,'_required_capabilities', true);
	if((count($required_capabilities) > 0) and ($required_capabilities[0] != '')) {
		$post_access = false;	  
		$members_length = (array)get_user_meta($user_id,'_subscription_ends');
		if(is_array($members_length) && !empty($members_length[0])){
			$members_length = $members_length[0];
		}
		foreach($required_capabilities as $capability ) {
			if($capability != null)
			{
				if ((int)mktime() < (int)$members_length[$capability] || $members_length[$capability] == 'never' ){
					$post_access = true;
					break;					
				}
			}
	  	}
	}
	return $post_access;
}
*/
function pro_polls_get_users_with_access($post_id)
{
	global $wpdb;
	  $query = "SELECT ID, user_login FROM $wpdb->users order by ID";
	  //$totalitems = $wpdb->query($query); 
	  $all_users = $wpdb->get_results($query);
	  $allowed_users = array();
	  foreach($all_users as $objUser)
	  {
		  //disable check for access for now
		 // if(pro_polls_check_access($post_id, $objUser->ID))
		  	$allowed_users[]= $objUser;
	  }
	 return($allowed_users);
}

function pro_polls_columns($columns) {

    $columns['ppolls_questions'] = 'Questions';
    $columns['ppolls_results'] = 'Results';
    $columns['ppolls_shortcode'] = 'Shortcode';
    return $columns;
}

function pro_polls_show_columns($name) {
    global $post;
	//pro_polls_dump($name);
	$objPoll = new Ppolls_polls($post->ID);
				$questions = $objPoll->getQuestions();
				$question_count = count($questions);
				
				$votes_count  = $objPoll->getVoteCount();
				//$votes_count = count($votes);
    switch ($name) {
        case 'ppolls_questions':
		
            $views = "<a href='edit.php?post_type=".PRO_POLL_POST_TYPE_SLUG."&page=pro-polls-questions&poll_id=".$post->ID."'>$question_count Questions</a>";
            echo $views;
			break;
        case 'ppolls_results':
            $views = "<a href='edit.php?post_type=".PRO_POLL_POST_TYPE_SLUG."&page=pro-polls-results&poll_id=".$post->ID."'>$votes_count Submissions</a>";
            echo $views;
			
			break;
        case 'ppolls_shortcode':
            $views = "[pro_polls id=\"".$post->ID."\"]";//get_post_meta($post->ID, 'views', true);
            echo $views;
			
			break;
    }
}

function pro_polls_get_custom_post_type_template($single_template) {
     global $post;

     if ($post->post_type == PRO_POLL_POST_TYPE_SLUG) {
          $single_template = PRO_POLL_PLUGIN_DIR . '/themes/single-poll.php';
     }
     return $single_template;
}

//[foobar]
function pro_polls_show_with_shortcode( $atts ){
	
	$a = shortcode_atts( array(
        'id' => '-1' 
    ), $atts ); 
	$poll_id = $a['id'];
	$objPoll = new Ppolls_polls($poll_id);
	if($objPoll ->isValid == false)
		return "<div class='polls_submit_error'>Invalid Poll.</div>";
	
	wp_enqueue_style( "pro_polls_styles");
	$user_id = get_current_user_id(); 
	$user_info = get_userdata($user_id); 
	$post_access = true;// pro_polls_check_access($poll_id, $user_id); Setting to tru now because no capabilites have been checked
	//
	ob_start();
	?>
	<div class='pro-poll pro-poll-<?php echo $poll_id;?>' id='pro-poll-<?php echo $poll_id;?>'>
			 <h3 class='pro-poll' >  <?php echo $objPoll->title; ?></h3> <?php
			if($post_access === false ) {
				echo "<div class='polls_submit_error'>Only logged in users can vote on this poll.</div>";
			}
			else
			{
				$questions = $objPoll->getQuestions();
				$hasUserAlreadySubmitted =  $objPoll->checkIfUserSubmittedAlready($user_id);
				if($hasUserAlreadySubmitted)
				{
					echo "<div class='polls_submit_error'>You have already submitted your entry on this poll. </div>";
				}
				else
				{
					$show_question = true;				
					if(isset($_POST['btnSubmitPoll']) && $_POST['proPollId'] == $poll_id )
					{
						$nonce = $_REQUEST['_wpnonce']; 
						if ( !wp_verify_nonce( $nonce, 'submit-poll-'.$poll_id ) ) {
						
							echo "<div class='polls_submit_error'>Invalid access</div>";
						
						} else {
						 
							$validate = true; 
							foreach($questions as $question)
							{
								$qid = $question['id'];
								if(!isset($_POST['ppolls_question'][$qid]))
								{
									$validate = false;
									$questions[$qid]['error'] = "Please select one of the option";
								}						
							}
							if($validate)
							{
								//pro_polls_dump($_POST);
								$answers = serialize($_POST['ppolls_question']);
								$save_result =  $objPoll->saveSubmissions($user_id, $answers);
								if($save_result !== false)
								{
									$objPoll->sendSubmissionEmailToAdmin($user_id, $answers);
									$show_question = false;
									echo "<div class='polls_submit_notification'>Thanks for submitting Poll.</div>";	
								}
								else
								{
									echo "Couldn't save polls result. You must have already entered this poll.";
								} 
							}
							else
							{
								echo "<div class='polls_submit_error'>You have one or more errors.</div>";	
							}
						}
					}
					if(count($questions)>0)
					{
						if($show_question == true)
						{?>
                            <form action="#poll-<?php echo $poll_id;?>" method="post"> <?php
                            foreach($questions as $question)
                            {
                                echo "<div  class='ppolls_question_section' id='ppolls_question_section_".$question['id']."'>";
                                echo "<p  class='ppolls_question' id='ppolls_question_".$question['id']."'>".$question['question'];
                                if(isset($question['error']) && $question['error']!="")
                                {
                                    echo " <span class='ppolls_val_error'>".$question['error']."</span>";
                                }
                                echo "</p>";
                                echo "<ul class='ppolls_options' id='ppolls_option_".$question['id']."'>";
                                foreach($question['answers'] as $answer)
                                {
                                    $answer = str_replace("\n", "", $answer);
                                    $answer = str_replace("\r", "", $answer);
                                    if($_POST['ppolls_question'][$question['id']] == $answer)
                                        $checked = "checked='checked'";
                                    else
                                        $checked ="";
                                    echo "<li>";
                                    echo "<input type='radio' name='ppolls_question[".$question['id']."]' value='$answer' $checked >$answer";
                                    echo "</li>";
                                    
                                }
                                echo "</ul>";
                                echo "</div>";
                            } ?>
                            <input type="hidden" name="proPollId" value="<?php echo $poll_id;?>" />
                            <input type="submit" name="btnSubmitPoll" value="Submit Poll" />
                            <?php wp_nonce_field( 'submit-poll-'.$poll_id );	?>
                            </form> <?php
						}
					}
					else
					{
						echo "<div class='polls_submit_error'>No questions on this poll yet.</div>"; 
					}
				}
				
			}//end of checking for access ?> 
	</div>
    <?php
	return ob_get_clean(); 
} 
?>