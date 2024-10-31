<?php
class Ppolls_polls {
	
	private $poll_id;
	public $title;
	public $isValid;
	
	function __construct($poll_id)
	{
		$this->poll_id = $poll_id;
		$post = get_post($poll_id);
		if($post == NULL || $post->post_type != PRO_POLL_POST_TYPE_SLUG  )		
			$this->isValid = false;
		else
		{			
			$this->isValid = true;
			$this->title = $post->post_title;
		}
	}
	
	function getQuestions()
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->posts WHERE post_type='ppolls_question' AND post_parent='".$this->poll_id."' order by menu_order ASC";
		$questions = array();
		$objQuestions = $wpdb->get_results($query);
		foreach($objQuestions as $objQuestion)
		{
			$id = $objQuestion->ID;
			$question_title = $objQuestion->post_title;
			$answers = unserialize($objQuestion->post_content);
			$questions[$id] = array("id"=>$id, "question"=>$question_title, "answers"=>$answers);			
		}
		return $questions;		
	}
	
	function checkIfUserSubmittedAlready($user_id)
	{
		global $wpdb;
		$query = "SELECT COUNT(id) from ".PRO_POLL_RESULT_TABLE." WHERE poll_id='".$this->poll_id."' AND user_id='$user_id'";
		//echo $query;
		$submitted_count = $wpdb->get_var($query);
		//echo "uuuu".$submitted_count;
		//user submitted this poll already. dont save new submission.
		if($submitted_count >0)
			return true;
		else
			return false;
	}
	
	function saveSubmissions($user_id, $answers)
	{
		global $wpdb;
		//check to see if this user has already submitted this poll.
		$hasUserAlreadySubmitted = $this->checkIfUserSubmittedAlready($user_id);
		//user submitted this poll already. dont save new submission.
		if($hasUserAlreadySubmitted)
			return false;
		
		$insert = array();
		$insert['poll_id'] = $this->poll_id;
		$insert['user_id'] = $user_id;
		$insert['answers'] = $answers;
		$insert['status'] = '1';
		return $wpdb->insert(PRO_POLL_RESULT_TABLE, $insert);		
	}
	
	function getVotes()
	{
		global $wpdb; 
		
		$query = "SELECT * FROM ".PRO_POLL_RESULT_TABLE." WHERE poll_id='".$this->poll_id."' order by submitted_time ASC";
		$votes = array();
		$objResults = $wpdb->get_results($query);
		foreach($objResults as $objResult)
		{
			$id = $objResult->ID;
			$answers = unserialize($objResult->answers);
			foreach($answers as $qid=>$answer)
			{
				if(!isset($votes[$qid]))
					$votes[$qid] = array();
				$votes[$qid][]= $answer;
				
			}				
		}
		//var_dump($objResults);
		return $votes;		
	}	
	function getVoteCount()
	{
		global $wpdb; 
		
		$query = "SELECT count(ID) as cnt FROM ".PRO_POLL_RESULT_TABLE." WHERE poll_id='".$this->poll_id."' order by submitted_time ASC";
		$votes = array();
		$objResults = $wpdb->get_results($query);
		 $objResult = $objResults[0];
		 return $objResult->cnt; 		
	}
	function getResults()
	{
		$questions = $this->getQuestions();
		$votes = $this->getVotes();
		$results = array();
		foreach($questions as $qid =>$question)
		{
			$results[$qid] = array();
			$results[$qid]["question"] = $questions[$qid]["question"];	
			if(isset($votes[$qid]))
				$votes_for_this_question = $votes[$qid];
			else
				$votes_for_this_question = array();
			$results_for_this_question = array_count_values($votes_for_this_question);
			
			$answers = array();
			foreach($questions[$qid]["answers"] as $existing_answer)
			{
				$existing_answer = trim($existing_answer);
				//echo("-$existing_answer**". $results_for_this_question[$existing_answer]);
				
				if(isset($results_for_this_question[$existing_answer]))
					$vote_count = $results_for_this_question[$existing_answer];
				else
					$vote_count = '0';
				$answers[] = array("answer"=>$existing_answer, "vote_count"=> $vote_count, "total_votes" =>count($votes[$qid]));
			} 
			$results[$qid]["answers"] = $answers;
			//dump($results[$qid]);
		}
		return $results;		
	}
	
	function sendSubmissionEmailToAdmin($user_id, $answers)
	{
		// Example using the array form of $headers
		// assumes $to, $subject, $message have already been defined earlier...
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		$to = get_option('ppolls_admin_email', 'voting@arffwg.org');
		$subject = "New Vote posted";
		$user_details = get_userdata( $user_id );
		$first_name = $user_details->user_firstname;
		$last_name = $user_details->user_lastname;
		$company = $user_details->company;
		$email = "";
		$email .= "<h3>New Poll Submitted.</h3>";
		$email .= "<p>Name: ".$first_name." ". $last_name."</p>";
		$email .= "<p>Comapny: ".$company."</p>"; 
		$email .= $this->getFormattedEmail($answers);
		 
		
		wp_mail( $to, $subject, $email, $headers );
	}
	function getFormattedEmail($answer_string)
	{
		$answer_details = $this->getAnswerDetails($answer_string);
		$det = array();
		$det[] = "<table style='border-collapse:collapse; border:1px solid #999' border='1px'>";
		foreach ($answer_details as $answers)
		{
			$det[] = "<tr><td><strong>".$answers[0]. "</strong></td><td>". $answers[1]."</td></tr>";
		}
		$det[] = "</table>"; 
		return (implode("\n", $det));
		
	}
	function getAnswerDetails($answer_string)
	{
		$answers = unserialize($answer_string);
		$details = array();
		foreach($answers as $question_id=>$answer)
		{
			$question_details = wp_get_single_post($question_id);
			$details[]= array($question_details->post_title, $answer);			
		}
		return $details;
	}

} ?>