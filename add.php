<?php
require_once('../../config.php');
//require_once('../../lib/filelib.php');
require_once('lib.php');
//include formclass.php
global $DB, $COURSE, $PAGE, $OUTPUT, $CFG, $_REQUEST,$_SESSION ,$USER;
require_once($CFG->dirroot . '/local/testimonial/form.php');

if (is_siteadmin()) {
    
    $PAGE->set_pagelayout('admin');
    
} else {
    
    $PAGE->set_pagelayout('user');
    
}

$PAGE->set_title("Testimonial");
$PAGE->set_heading("Add Testimonial");
$PAGE->set_url($CFG->wwwroot . '/local/testimonial/add.php');


if (is_siteadmin() || user_has_role_assignment($USER->id, 9)) {
    
    $coursenode = $PAGE->navbar->add('Testmonial', new moodle_url('/local/testimonial/list.php'));
    
} else {
    
    $coursenode = $PAGE->navbar->add('Testmonial', new moodle_url('/local/testimonial/list.php'));
    
}

$coursenode = $PAGE->navbar->add('Add New Testmonial');


$maxbytes = $CFG->maxbytes;

require_login();

if (is_siteadmin() || user_has_role_assignment($USER->id, 9)) {
    //echo "allow";
} else {
    redirect($CFG->wwwroot . '/my/', 'You do not have permission.');
}



if (isset($_GET['deleteId'])) {
    $delId   = $_GET['deleteId'];
    $delQry  = "DELETE FROM mdl_lib_video_category WHERE id = $delId";
    $execQry = $DB->execute($delQry);
    redirect($CFG->wwwroot . "/blocks/video/category_list.php?id=$courseid", "Deleted Successfully");
}

$context = context_system::instance();
$instance = new stdClass();
    		$instance->id = null; 
		
$mform = new simplehtml_form(new moodle_url('/local/testimonial/add.php'));


if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/testimonial/list.php');
} else if ($fromform = $mform->get_data()) {

    if(!is_siteadmin()){
        $uid = "SELECT * FROM mdl_company_users where userid = $USER->id and companyid = ".$_SESSION['company_id_session'];
        $userid = $DB->get_record_sql($uid);
        $companyid = $userid->companyid;
    }
    
    $record1 = new stdClass();
    if(is_siteadmin()){
        $record1->company = $fromform->company;
    }else{
        $record1->company = $companyid;	
    }
    $record1->author = $fromform->author;
    $record1->designation = $fromform->designation;
    $record1->description = $fromform->description;
    $record1->timecreated = time();
    $record1->embed_url = $fromform->embed_url;
    $record1->attachoption = $fromform->attachoption;
   
 //   print_r($record1);die;
    $lastinsertid = $DB->insert_record('local_testimonial', $record1);
  
if (!empty($fromform->attachment)) {
    file_save_draft_area_files($fromform->attachment,
                               $context->id,
                               'local_testimonial',
                               'attachment',
                               $lastinsertid,
                               array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
}
redirect($CFG->wwwroot.'/local/testimonial/list.php','Added Successfully');
}else{
    echo $OUTPUT->header();
    echo '<h4 style="padding:10px"> Add New Testimonial</h4>';
$mform->display();
}

 
echo $OUTPUT->footer();
?>
