<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
class Siteadmin extends CI_Controller 
{
	function __construct() 
	{
		parent::__construct();
		$this -> load -> library('session');
		$this->load->model('admin_model');
		$this -> load -> database();
		$this -> no_cache();
		$this->redirect();
		$this->load->library('form_validation');	
	}
		protected function no_cache() 
	{
		//this function is for clearing cache
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
	}
	public function redirect()
	{
		//This function is used for redirecting a function
		$email = $this->session->userdata('email_id');
		if($email =='')
		{
			redirect('admin/index');
		}
	}
	public function image() {
		//This function is used to convert base64 to image and upload to directory
		if (isset($_FILES['file']['name'])) {
			if ($_FILES['file']['size'] > 2097152) {
				$value = "largesize";
				echo json_encode(array("value" => $value));
			} else {
				list($width, $height, $type) = getimagesize($_FILES['file']['tmp_name']);
				if ($type != 1 && $type != 2 && $type != 3) {
					$value = "type";
					$filename = $type;
					//"Your image file was too large.";
					echo json_encode(array("filename" => $filename, "value" => $value));
				} else {
					$id = time();
					$filename = $id . $_FILES['file']['name'];
					if (move_uploaded_file($_FILES['file']['tmp_name'], "./temp_image/" . $filename)) {
						$value = "success";
						echo json_encode(array("fileId" => $id, "value" => $value, "filename" => $filename, "actuleFile" => $_FILES['file']['name']));
					} else {
						$value = "failedlll";
						echo json_encode(array("fileId" => $id, "value" => $value, "filename" => "", "actuleFile" => $_FILES['file']['name']));
					}
				}

			}// end for file size	*/
		}
	}
	public function home()
	{
		$this->load->view('admin/home');
	}
	public function viewUser()
	{
		//This fucntion is for view all the app user
		$result['data']=$this->admin_model->getAllRecord('user');
		
		$this->load->view('admin/view_user',$result);
	}
	public function editUser()
	{
		//This function is for reset the user's password
		$user_id=$this->uri->segment(3);
		$result['user_detail']=$this->admin_model->getRecord('user',array('user_id'=>$user_id));
		//This is for form validation
		if($this->form_validation->run('reset_pwd')==FALSE)
		{
			$this ->load->view('admin/update_user',$result);	
		}
		else 
		{
			if(isset($_POST['submit']))
			{
				//now getting the value from view
				//$password=$this->input->post('password');
				$first_name=$this->input->post('first_name');
				$last_name=$this->input->post('last_name');
				$email_id=$this->input->post('email_id');
				$gender=$this->input->post('gender');
				$dob=$this->input->post('dob');
				
				//now update the password 
				$data=array(
							//'password'=>$password,
							'first_name'=>$first_name,
							'last_name'=>$last_name,
							'email_id'=>$email_id,
							'gender'=>$gender,
							'dob'=>$dob);
				$up_result=$this->admin_model->updateRecord('user',$data,array('user_id'=>$user_id));
				
				if($up_result==0)
				{
					$this->session->set_flashdata('message','User updated');
					redirect('siteadmin/editUser/'.$user_id);
				}
				else 
				{
					$this->session->set_flashdata('message','User updated');
					redirect('siteadmin/editUser/'.$user_id);	
				}
				
			}
		}
		
	}
	public function resetPassword()
	{
		//This function is for reset the user's password
		$user_id=$this->uri->segment(3);
		
		$password=$this->admin_model->genRandomPassword(6);
		$enc_password=$this->admin_model->encrypt_decrypt('encrypt', $password);
		//now update this password to the database
		$up_result=$this->admin_model->updateRecord('user',array('password'=>$enc_password),array('user_id'=>$user_id));
		
		if($up_result==0)
		{
			$this->session->set_flashdata('message','Password not updated');
			redirect('siteadmin/viewUser');
		}
		else 
		{
			//getting the user detail of the user_id
			$result=$this->admin_model->getRecord('user',array('user_id'=>$user_id));
			if($result==0)
			{
				$this->session->set_flashdata('message','Password not updated');
				redirect('siteadmin/viewUser');
			}
			else 
			{
				$email_id=$result[0]['email_id'];
				$message="Your New Password is :".$password;
				$subject="Reset Password";
				//now mail this password to the user
				$this->admin_model->sendEmail($email_id,$message,$subject);
				
				$this->session->set_flashdata('message','Password updated');
				redirect('siteadmin/viewUser');	
			}
			
				
		}
	}
	public function blockUser()
	{
		//this function is for block the user according to the user id
		$user_id=$this->uri->segment(3);
		$up_result=$this->admin_model->updateRecord('user',array('status'=>1),array('user_id'=>$user_id));
				
		if($up_result==0)
		{
			$this->session->set_flashdata('message','User already blocked');
			redirect('siteadmin/viewUser');
		}
		else 
		{
			$this->session->set_flashdata('message','User blocked');
			redirect('siteadmin/viewUser');	
		}
		
	}
	public function activeUser()
	{
		//this function is for block the user according to the user id
		$user_id=$this->uri->segment(3);
		$up_result=$this->admin_model->updateRecord('user',array('status'=>0),array('user_id'=>$user_id));
				
		if($up_result==0)
		{
			$this->session->set_flashdata('message','User already active');
			redirect('siteadmin/viewUser');
		}
		else 
		{
			$this->session->set_flashdata('message','User active');
			redirect('siteadmin/viewUser');	
		}
		
	}
	public function compare_date()
	{
		//call back function for voting and entry end date compare
		if(isset($_POST['start_date']) and isset($_POST['end_date']))
		{
			if($_POST['end_date'] >=$_POST['start_date'])
			{
				return TRUE;
			}
			else 
			{
				$this->form_validation->set_message('compare_date','End date must be greater than Strat date');
				return FALSE;	
			}
			
		}
	}
	public function addMedicine()
	{
		//This funciton is used to add medication for the user
		$user_id=$this->uri->segment(3);
		if($this->form_validation->run('add_medication')==FALSE)
		{
			//validation error
			$this->load->view('admin/add_medication');
		}
		else 
		{
			if(isset($_POST['submit']))
			{
				//now getting the vlaue form view
				$name=$this->input->post('name');
				$dosage_time=$this->input->post('dosage_time');
				$segment=$this->input->post('segment');
				$food=$this->input->post('food');
				$medicine_form=$this->input->post('medicine_form');
				$dosage_quantity=$this->input->post('dosage_quantity');
				$start_date=$this->input->post('start_date');
				$end_date=$this->input->post('end_date');
				
				$result=$this->admin_model->getRecord('medicine',array('name'=>$name,'user_id'=>$user_id));
				if($result!=0)
				{
					//This medicine is already exist
					$this->session->set_flashdata('message','Medicine already exist');
					redirect('siteadmin/viewUser');
				}	
				else 
				{
					//Rename a image file
		
					if($_FILES['image']['name']!='')
					{
						$imanename = $_FILES['image']['name'];
			
						
						$temp = explode(".", $_FILES["image"]["name"]);
						$newfilename = rand(1, 99999) . '.' . end($temp);
						$newfilename;
					}
					else 
					{
						$newfilename="";
					}	
					//This is for upload the image
					
					//$path= './uploads/'.$newfilename;
					//$upload = copy($_FILES['image']['tmp_name'], $path);
					
					
					
					$this -> load -> library("upload");//load library to upload files

					$config['upload_path'] = './uploads/';//dir path
					$config['allowed_types'] = "gif|jpg|png|jpeg";
					$config['file_name'] = $newfilename;
					$this -> upload -> initialize($config);
					
					if (!$this -> upload -> do_upload('image', $config)) 
					{
						$this -> session -> set_flashdata('message', 'Error Uploading files');
						redirect('Siteuser/addMedicine');
					} 
					else 
					{
						
						$data = array('upload_data' => $this -> upload -> data());
						$imanename = $_FILES['image']['name'];
		
					}
					
					
					$medi_data=array(
									'user_id'=>$user_id,
									'name'=>$name,
									'dosage_time'=>$dosage_time,
									'food'=>$food,
									'medicine_form'=>$medicine_form,
									'dosage_quantity'=>$dosage_quantity,
									'start_date'=>$start_date,
									'end_date'=>$end_date,
									'picture'=>$newfilename,
									'segment'=>$segment
									);
				
					$medi_result=$this->admin_model->saveRecord('medicine',$medi_data);
					
					if($medi_result==0)
					{
						$this->session->set_flashdata('message','Medicine could add this time');
						redirect('siteadmin/viewUser');
					}	
					else 
					{
						$this->session->set_flashdata('message','Medicine added successfully');
						redirect('siteadmin/viewUser');
					}
				}
				
			}
		}
		
	}
	public function viewMedicine()
	{
		//This function is for view currently active medicine
		//get the user id from uri
		$user_id=$this->uri->segment(3);
		//now get the data from database
		$result['data']=$this->admin_model->getRecord('medicine',array('user_id'=>$user_id,'end_date >'=>date('Y-m-d')));
		$this->load->view('admin/view_medicine',$result);
	}
	public function viewHistory()
	{
		//This function is for view medicine
		//get the user id from uri
		$user_id=$this->uri->segment(3);
		//now get the data from database
		$result['data']=$this->admin_model->getRecord('medicine',array('user_id'=>$user_id,'end_date <'=>date('Y-m-d')));
		$this->load->view('admin/view_history',$result);
	}
	public function deleteMedicine()
	{
		//this function is for delete the medicine 
		//getting the medicine from url
		$medicine_id=$this->uri->segment(4);
		$user_id=$this->uri->segment(3);
		//now delete the record
		$result=$this->admin_model->deleteRecord(array('medicine_id'=>$medicine_id),'medicine');
		if($result==0)
		{
			//medicine not deleted
			$this->session->set_flashdata('message','Record can not delete');
			redirect('siteadmin/viewMedicine/'.$user_id);
		}
		else 
		{
			//medicine deleted	
			$this->session->set_flashdata('message','Record deleted');
			redirect('siteadmin/viewMedicine/'.$user_id);
		}
	}
}	
?>