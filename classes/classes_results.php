<?php
class PpollsResults extends WP_List_Table {

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	 private  $poll_id;
	
	 
	 function __construct($poll_id) {
		 parent::__construct( array(
		'singular'=> 'wp_list_question', //Singular label
		'plural' => 'wp_list_questions', //plural label, also this well be one of the table css class
		'ajax'	=> false //We won't support Ajax for this table
		) );
		
		$this->poll_id = $poll_id;
		
	 }
	 
	 /**
 * Add extra markup in the toolbars before or after the list
 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
 */
function extra_tablenav( $which ) {
	if ( $which == "top" ){
		//The code that goes before the table is here
		//echo"Hello, I'm before the table";
	}
	if ( $which == "bottom" ){
		//The code that goes after the table is there
		//echo"Hi, I'm after the table";
	}
}

/**
 * Define the columns that are going to be used in the table
 * @return array $columns, the array of columns to use with the table
 */
function get_columns() {
	return $columns= array( 
		'col_question'=>('Questions'),
		'col_answers'=>('Vote Results')
	);
}
/**
 * Decide which columns to activate the sorting functionality on
 * @return array $sortable, the array of columns that can be sorted by the user
 */
public function get_sortable_columns() {
	return $sortable = array( 
	);
}

/**
 * Prepare the table with different parameters, pagination, columns and table elements
 */
function prepare_items()   {
                global $wpdb, $_wp_column_headers;
                $screen = get_current_screen();
 
$objPolls = new Ppolls_polls($this->poll_id);
$results = $objPolls->getResults();
               
                /* — Register the Columns — */
                $columns = $this->get_columns();
                $hidden = array();
                $sortable = $this->get_sortable_columns();
                $this->_column_headers = array($columns, $hidden, $sortable);
 
                /* -- Fetch the items -- */
                $this->items = $results;
}
	 
	 /**
 * Display the rows of records in the table
 * @return string, echo the markup of the rows
 */
function display_rows() {

	global $wpdb, $_wp_column_headers;
	//Get the records registered in the prepare_items method
	$records = $this->items;

	//Get the columns registered in the get_columns and get_sortable_columns methods
	list( $columns, $hidden ) = $this->get_column_info();

		
	//Loop for each record
	if(!empty($records)){foreach($records as $rec){

		//Open the line
 
        echo '<tr id="record_'.$rec->ID.'">';
		foreach ( $columns as $column_name => $column_display_name ) {
			//die("ddddddddd");

			//Style attributes for each col
			$class = "class='$column_name column-$column_name'";
			$style = "";
			if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
			$attributes = $class . $style;

			//edit link
			$editlink  = 'edit.php?post_type='.PRO_POLL_POST_TYPE_SLUG.'&page=pro-polls-questions&poll_id='.$this->poll_id.'&qid='.$rec->ID.'';
			$answers = ($rec['answers']);
			$formatted_answers = array();
			foreach($answers as $answer_det)
			{
				$vote_count = (int)$answer_det['vote_count'];
				$total_votes = (int)$answer_det['total_votes'];
				if($total_votes ==0)
					$percentage = 0;
				else
				{
					$percentage = ceil( $vote_count / $total_votes * 100);
				}
				$count_text = $answer_det['answer']. " - ". $vote_count." / ".$total_votes;
				$graph ='<div>'.$count_text.'<div style="width:200px; border:1px solid #111; height: 10px; background: #fee;"><span style="height:10px;width:'.$percentage.'%; background:#0f0; float:left;">&nbsp;</span></div></div>';		

				$formatted_answers[] =  $graph;
			}
			$formatted_answers = implode("<br />\n", $formatted_answers);
			//Display the cell
			switch ( $column_name ) {
				case "col_question": echo '<td '.$attributes.'>'.stripslashes($rec['question']).'</td>'; break;
				case "col_answers": echo '<td '.$attributes.'>'.$formatted_answers.'</td>'; break; 
			}
		}

		//Close the line
		echo'</tr>';
	}}
}



} ?>