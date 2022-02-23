<?php
require_once('../../config.php');
//require_once('../../lib/filelib.php');
require_once('lib.php');
//include formclass.php
global $DB, $PAGE, $OUTPUT, $CFG,$USER;
require_once($CFG->dirroot . '/local/testimonial/form.php');
?>
<style>


.fa-file-o:before {
    content: "";
    font-family: 'FontAwesome';
}
</style>
<?php

if (is_siteadmin()) {
    
    $PAGE->set_pagelayout('admin');
    
} else {
    
    $PAGE->set_pagelayout('user');
    
}

$PAGE->set_title("Testimonial");
$PAGE->set_heading("Edit Testimonial");
$PAGE->set_url($CFG->wwwroot . '/local/testimonial/edit.php');

$coursenode = $PAGE->navbar->add('Testimonial list', new moodle_url('/local/testimonial/list.php'));
$coursenode = $PAGE->navbar->add('Edit Testimonial');
$updateid   = optional_param('updateid', 0, PARAM_INT);
$id   = optional_param('updateid', 0, PARAM_INT);

require_login();
if (is_siteadmin() || user_has_role_assignment($USER->id, 9)) {
    //echo "allow";
} else {
    redirect($CFG->wwwroot . '/my/', 'You do not have permission.');
}



$context = context_system::instance();
$maxbytes = $CFG->maxbytes;

//echo $updateid;die;

if ($updateid) {

    $instance = $DB->get_record('local_testimonial', array(
        'id' => $updateid
    ), '*', MUST_EXIST);
 //   echo '<pre>';print_r($instance);die;
    $draftuser_banner = file_get_submitted_draft_itemid('attachment');
    
    file_prepare_draft_area($draftuser_banner,
    $context->id,'local_testimonial','attachment', $instance->id,  array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
    $instance->attachment = $draftuser_banner;
    
    
        $mform = new simplehtml_form(new moodle_url('/local/testimonial/edit.php'), array( 'id' => $id,
        $instance
        ));

}

else {
	
    $mform = new simplehtml_form(new moodle_url('/local/testimonial/edit.php'), array('id' => $id));
}



//Form processing and displaying is done here

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/testimonial/list.php');
} else if ($fromform = data_submitted()) {
    if(!is_siteadmin()){
        $uid = "SELECT * FROM mdl_company_users where userid = $USER->id and companyid = ".$_SESSION['company_id_session'];
        $userid = $DB->get_record_sql($uid);
        $companyid = $userid->companyid;
    }
    $record1                 = new stdClass();
    $record1->id             = $fromform->updateid;
    if(is_siteadmin()){
        $record1->company = $fromform->company;
    }else{
        $record1->company = $companyid;	
    }
    $record1->author    = $fromform->author; 
    $record1->designation = $fromform->designation;
    $record1->description        = $fromform->description;
    $record1->embed_url      = $fromform->embed_url;
    $record1->attachoption = $fromform->attachoption;
    $record1->timemodified      = time();
    $filename   = $mform->get_new_filename('attachment');
//   echo '<pre>';print_r($instance);die;
   $filename = str_replace(' ', '', $filename);
	$target_path = 'category_images/'.$newfilename;
	$imageFileType = pathinfo($target_path, PATHINFO_EXTENSION);
	//echo '<pre>';print_r($record1);
   
    if($instance->attachoption != $fromform->attachoption){
        if($instance->attachoption == 1){
            $fs = get_file_storage();
		$fs->delete_area_files(context_system::instance()->id, 'local_testimonial', 'attachment', $instance->id);
        }

        elseif($instance->attachoption == 2){
            $record1->embed_url=null;

        }

    }


    if (!empty($fromform->attachment)) {
        file_save_draft_area_files($fromform->attachment,
                                   $context->id,
                                   'local_testimonial',
                                   'attachment',
                                   $updateid,
                                   array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));
    }

    $updated = $DB->update_record('local_testimonial', $record1);

  
   redirect($CFG->wwwroot . '/local/testimonial/list.php', 'Updated Successfully');
   

    
} else {
    echo $OUTPUT->header();
    echo '<h4 style="padding:10px"> Edit Testimonial</h4>';
    $mform->display();
}

echo $OUTPUT->footer();
?>
