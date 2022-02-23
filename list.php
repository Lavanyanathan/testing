<style>
.desc{
    word-break: break-word;
    }
  .lastcol{
      width:100px;
  }
    </style>

<?php
require_once('../../config.php');
require_once('lib.php');
global $DB, $PAGE, $OUTPUT, $CFG,$_SESSION;
if(is_siteadmin()){
	
	$PAGE->set_pagelayout('admin');
	 
}else{
	
	$PAGE->set_pagelayout('user');
	
}

$page        = optional_param('page', 0, PARAM_INT);
$perpage     = 10;
$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', '', PARAM_ALPHANUM); // Confirmation hash.

$PAGE->set_title("Testimonial List");
$PAGE->set_heading("Testimonial List");
$PAGE->set_url($CFG->wwwroot . '/local/testimonial/list.php');

require_login();
$PAGE->set_heading("Shriram Leadersip Academy - LMS");
$coursenode = $PAGE->navbar->add('Testimonial');
//$coursenode = $PAGE->navbar->add('Document List', new moodle_url('/local/testimonial/list.php'));

$targetpage = "list.php"; 


if(is_siteadmin() || user_has_role_assignment($USER->id, 9)){

$deleteId = optional_param('deleteid', 0, PARAM_INT);
if($deleteId && !$delete) {
    $delId = $deleteId;

    $checkdel=$DB->get_record_sql("SELECT * FROM mdl_local_testimonial  where id=$deleteId");
   
    if($checkdel){
        
        $continueurl = new moodle_url('/local/testimonial/list.php', array('id' => $deleteId, 'delete' => md5($checkdel->timecreated)));
        $cancel=new moodle_url('/local/testimonial/list.php');
        $continuebutton = new single_button($continueurl, get_string('delete'), 'post');
        $concat = $checkdel->attachoption == 1  ? 'Attachment' : 'Embed Url';
        //echo '<pre>';print_r(FILE_ATTACHMENT);die;

        $message = "Are you absolutely sure you want to completely delete this Testimonial and all the data ? ".$concat;
        echo $OUTPUT->header();
        echo $OUTPUT->confirm($message,$continuebutton,$cancel);
        echo $OUTPUT->footer();
        exit;
        //echo '<pre>';print_r($continueurl);die;
    }
  
    
    
}


if($delete){
    $delId = $id;
//echo $delId;die;
$delQry = "DELETE FROM mdl_local_testimonial WHERE id = $delId";
  $execQry = $DB->execute($delQry);
  redirect($CFG->wwwroot."/local/testimonial/list.php","Deleted Successfully");
}
echo $OUTPUT->header();
echo '<a href="'.$CFG->wwwroot . '/local/testimonial/add.php"><button class="btn btn-primary float-right pb-2 mb-2"  >Add Testimonial</button></a>';

if(is_siteadmin()){
    $getdoclist = "SELECT * FROM mdl_local_testimonial  ORDER  BY id DESC";
}else{
    $getdoclist = "SELECT * FROM mdl_local_testimonial WHERE company = ".$_SESSION['company_id_session']."   ORDER  BY id DESC";
}
$test_arr = $DB->get_records_sql($getdoclist);
//$test_arr=$DB->get_records_sql("select * from mdl_testimonial ");


$table = new html_table();

$table->head = array(
    'S.no',
	'Company',
	'Author',
    'Designation',
	'Description',
	'Attachment',
    
	'Action',
    
	
);
$table->colclasses = array(
	'leftalign ',
    'leftalign ',
    'leftalign ',
    'leftalign',
    'leftalign',
    'leftalign',
    'leftalign',
    'leftalign'
);
$table->id = 'testimonial_list';

$table->attributes['class'] = 'admintable generaltable';

$offset = ($page * $perpage) + 1;

$baseurl = new moodle_url('/local/testimonial/list.php', array(
    'id'=>$id,
    'perpage' => $perpage,
    'page' => $page
));
$data = array();
$i=1;

foreach ($test_arr as $tlist){
//  echo '<pre>';print_r($tlist->id);die;
$edit = '';
    $row = [];
    $row[]=$i;
if($tlist->company !=0){
    $comp_name=$DB->get_record_sql("select name from mdl_company where id =$tlist->company");
    $row[]=$comp_name->name;
}else{
    $row[]='All';
}

    
	$row[]=$tlist->author;
    $row[]=$tlist->designation;
	$row[]=$tlist->description;
    
    //$image = "'.$tlist->embed_url.'";
    $fs = get_file_storage();
    $context = context_system::instance();
   // $image = '';
    if ($files = $fs->get_area_files($context->id, 'local_testimonial', 'attachment', $tlist->id, "timemodified", false)) {
        foreach ($files as $file) {
            $filename = $file->get_filename();
            $mimetype = $file->get_mimetype();
            $image = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/local_testimonial/attachment/' . $tlist->id . '/' . $filename);
            //echo '<pre>';print_r($image);die;
            $imageurl ="<a href='" . $image . "' target='_blank' >" .'<i class="fa fa-download text-center" aria-hidden="true"></i>'. "</a>";
            if((strpos($mimetype, 'image') !== false)){
                $imageurl ="<a href='" . $image . "' target='_blank' >" .'View'. "</a>";

            }
            if((strpos($mimetype, 'video') !== false)){
                $imageurl ="<a href='" . $image . "' target='_blank' >" .'View'. "</a>";

            }
        }
       
    } else{
        $filename ='url link';
        $imageurl='Video Url';
    }
    /*if($image){

        $attach=$image;
    }
    else{
        $attach=$tlist->embed_url;
    }*/
    $row[]=$imageurl;
  //  $row[]=$filename;
    $edit .= html_writer::link(new moodle_url('/local/testimonial/edit.php', array('updateid' => $tlist->id)), $OUTPUT->pix_icon('t/edit','edit'));
    $edit .=' | ';
    $edit .= html_writer::link(new moodle_url('/local/testimonial/list.php', array('deleteid' => $tlist->id)), $OUTPUT->pix_icon('t/delete','delete'));
   
    $row[]="$edit";
    $i++;
    $data[] = new html_table_row($row);
    
}

$table->id = 'testimonial_list';
$table->attributes['class'] = 'admintable generaltable';
$table->data = $data;
echo $OUTPUT->paging_bar(count($tlist), $page, $perpage, $baseurl);
echo "<div id='tableContainer'>";
echo html_writer::table($table);
echo "</div>";
echo $OUTPUT->paging_bar(count($tlist) , $page, $perpage, $baseurl);

}else { 
	echo "Access Denied"; 
}

echo $OUTPUT->footer();