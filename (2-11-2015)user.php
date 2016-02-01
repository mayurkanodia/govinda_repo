<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*class Home extends CI_Controller{*/

include (APPPATH . 'libraries/REST_Controller.php');

class User extends REST_Controller {

	public function __construct() 
	{
		parent::__construct();
		$this -> load -> model('user_model');
	}
	public function userLoginView_get()
	{
		//This function is for enable login to user
		$this->load->view("webservices/userlogin");
	}
	public function userLogin_post()
	{
		
		//this function is to enable login user
		//now get the information of user
		$mobile_numer	= $this->input->post('mobile_numer');
		$password		= $this->input->post('password');
		$device_token	= $this->input->post('device_token');
		$device_type	= $this->input->post('device_type');
		//now get the user detail from table
		$enc_password=$this->user_model->encrypt_decrypt('encrypt', $password);
		
		$data=array('mobile'=>$mobile_numer,'password'=>$enc_password);
		
		$result=$this->user_model->getRecord('user',$data);
		//echo $this->db->last_query();die;
		if($result==0)
		{
			//authentication faiure
			$user_re['message']="failure";	
		}
		else 
		{
			//authentication success
			//update the device token
			$this->user_model->updateRecord('user',array('device_token'=>$device_token,'device_type'=>$device_type,'status'=>0),array('user_id'=>$result[0]['user_id']));
			
			$user_re['message']		= "success";
			$user_re['user_id']		= $result[0]['user_id'];
			$user_re['facebook_id']	= $result[0]['facebook_id'];
			$user_re['google_id']	= $result[0]['google_id'];
			$user_re['twitter_id']	= $result[0]['twitter_id'];
			$user_re['first_name']	= $result[0]['first_name'];
			$user_re['last_name']	= $result[0]['last_name'];
			$user_re['dob']			= $result[0]['dob'];
			$user_re['email_id']	= $result[0]['email_id'];
			$user_re['mobile']		= $result[0]['mobile'];
			$user_re['image']		= $this->user_model->get_user_img_url('user_image',$result[0]['image']);
			$user_re['address']		= $result[0]['address1'];
			$user_re['address2']	= $result[0]['address2'];
			$user_re['state']		= $result[0]['state'];
			$user_re['city']		= $result[0]['city'];
			$user_re['pin']			= $result[0]['pin'];
			$user_re['gender']		= $result[0]['gender'];
			
		}
		echo json_encode($user_re);
	}
	public function socialLoginView_get()
	{
		//this view is for social login
		$this->load->view('webservices/social_login');
	}
	
	public function socialLogin_post()
	{
		//This function is used for social login
		//Now getting the value from view
		$facebook_id	= $this->input->post('facebook_id');
		$google_id		= $this->input->post('google_id');
		$twitter_id		= $this->input->post('twitter_id');
		$device_token	= $this->input->post('device_token');
		$first_name		= $this->input->post('first_name');
		$last_name		= $this->input->post('last_name');
		$email_id		= $this->input->post('email_id');
		$device_type	= $this->input->post('device_type');
		
		//Now checking this exist in database or not
		$data=array(
					'facebook_id'	=> $facebook_id,
					'google_id'		=> $google_id,
					'twitter_id'	=> $twitter_id);
					
		$result=$this->user_model->getRecord('user',$data);
		if($result==0)
		{
			//This user does not exist in data base
			//Now insert the user detail in data base
			$data=array(
					'facebook_id'	=> $facebook_id,
					'google_id'		=> $google_id,
					'twitter_id'	=> $twitter_id,
					'device_token'	=> $device_token,
					'first_name'	=> $first_name,
					'last_name'		=> $last_name,
					'email_id'		=> $email_id,
					'device_type'	=> $device_type,
					'created'		=> date('Y-m-d H:i:s'));
			$save_result=$this->user_model->saveRecord('user',$data);
			if($save_result==0)
			{
				//server error while inserting the data
				$post['message']='failure';
			}
			else 
			{
				//successful
				//now get the user detail for this user	
				$user_info=$this->user_model->getRecord('user',array('user_id'=>$save_result));
				//binding up the data in an array
				$post['message']		= "success";
				$post['user_id']		= $user_info[0]['user_id'];
				$post['facebook_id']	= $user_info[0]['facebook_id'];
				$post['google_id']		= $user_info[0]['google_id'];
				$post['twitter_id']		= $user_info[0]['twitter_id'];
				$post['first_name']		= $user_info[0]['first_name'];
				$post['last_name']		= $user_info[0]['last_name'];
				$post['email_id']		= $user_info[0]['email_id'];
				$post['mobile']			= $user_info[0]['mobile'];
				$post['dob']			= $user_info[0]['dob'];
				
				if($user_info[0]['image']=='')
				{
					$post['image']		= "no image";
				}
				else 
				{
					$post['image']= $this->user_model->get_user_img_url('user_image',$user_info[0]['image']);	
				}
				
				$post['address']		= $user_info[0]['address1'];
				$post['address2']		= $user_info[0]['address2'];
				$post['state']			= $user_info[0]['state'];
				$post['city']			= $user_info[0]['city'];
				$post['pin']			= $user_info[0]['pin'];
				$post['gender']			= $user_info[0]['gender'];
			}	
		}
		else 
		{
			//This user exist in data base	
			//Now update the device token
			$mobile			= $this->input->post('mobile');
			$gender			= $this->input->post('gender');
			$data=array(
						'device_token'=>$device_token,
						'device_type'=>$device_type,
						'first_name'=>$first_name,
						'last_name'=>$last_name,
						'email_id'=>$email_id,
						'mobile'=>$mobile,
						'gender'=>$gender
						
						);
			
			$this->user_model->updateRecord('user',$data,array('user_id'=>$result[0]['user_id']));
			
			//binding up the data in an array
			$post['message']		= "success";
			$post['user_id']		= $result[0]['user_id'];
			$post['facebook_id']	= $result[0]['facebook_id'];
			$post['google_id']		= $result[0]['google_id'];
			$post['twitter_id']		= $result[0]['twitter_id'];
			$post['first_name']		= $result[0]['first_name'];
			$post['last_name']		= $result[0]['last_name'];
			$post['email_id']		= $result[0]['email_id'];
			$post['mobile']			= $result[0]['mobile'];
			$post['dob']			= $result[0]['dob'];
			if($result[0]['image']=='')
			{
				$post['image']		= "no image";
			}
			else 
			{
				$post['image']= $this->user_model->get_user_img_url('user_image',$result[0]['image']);	
			}
			
			$post['address']		= $result[0]['address1'];
			$post['address2']		= $result[0]['address2'];
			$post['state']			= $result[0]['state'];
			$post['city']			= $result[0]['city'];
			$post['pin']			= $result[0]['pin'];
			$post['gender']			= $result[0]['gender'];
		}
		echo json_encode($post);
	}
	public function userRegisterView_get()
	{
		//This function is for user register view
		$this->load->view("webservices/userregister");
	}
	public function userRegister_post()
	{
		//This function is for user register 
		//now get the information from view
		$first_name		= $this->input->post('first_name');
		$last_name		= $this->input->post('last_name');
		$email_id		= $this->input->post('email_id');
		$phone_number	= $this->input->post('phone_number');
		$address1		= $this->input->post('address1');
		$address2		= $this->input->post('address2');
		$state			= $this->input->post('state');
		$city			= $this->input->post('city');
		$pin			= $this->input->post('pin');
		$gender			= $this->input->post('gender');
		$device_token	= $this->input->post('device_token');
		$device_type	= $this->input->post('device_type');
		$password		= $this->input->post('password');
		$date_of_birth	= $this->input->post('date_of_birth');
		
		$enc_password=$this->user_model->encrypt_decrypt('encrypt', $password);
		//now check the email is already registered or not
		$check=$this->user_model->getRecord('user',array('email_id'=>$email_id));
		if($check==0)
		{
			//email id does not exist
			//now check the mobile no is registerd or not
			$check1=$this->user_model->getRecord('user',array('mobile'=>$phone_number));
			if($check1==0)
			{
				//mobile no not registerd
				//Rename a image file
				if($_FILES)
				{
					if($_FILES['profile_image']['name']!='')
					{
						$imanename = $_FILES['profile_image']['name'];
			
						
						$temp = explode(".", $_FILES["profile_image"]["name"]);
						$newfilename = rand(1, 99999) . '.' . end($temp);
						$newfilename;
						//This is for upload the image
						
						$path= './user_image/'.$newfilename;
						$upload = copy($_FILES['profile_image']['tmp_name'], $path);
					}
					else 
					{
						$newfilename="";
					}
				}
				else 
				{
					$newfilename="";	
				}	
					//$password=$this->user_model->genRandomPassword(6);
					$data=array(
								'first_name'	=> $first_name,
								'last_name'		=> $last_name,
								'dob'			=>$date_of_birth,
								'email_id'		=> $email_id,
								'password'		=> $enc_password,
								'image'			=> $newfilename,
								'mobile'		=> $phone_number,
								'address1'		=> $address1,
								'address2'		=> $address2,
								'state'			=> $state,
								'city'			=> $city,
								'pin'			=> $pin,
								'gender'		=> $gender,
								'device_token'	=> $device_token,
								'device_type'	=> $device_type,
								'created'		=> date('Y-m-d H:I:s'));
					$result=$this->user_model->saveRecord('user',$data);
					if($result==0)
					{
						
						$post['message']="failure";	
					}
					else 
					{
						
						
						// now have to send and email containing the password
						//$subject="Account Varification";
						 //$this->user_model->sendEmail($email_id,$password,$subject);
						//now get the information of user
						$user_info=$this->user_model->getRecord('user',array('user_id'=>$result));
						if($user_info!=0)
						{
						
							$post['message']	= "success";
							$post['user_id']	= $user_info[0]['user_id'];
							$post['facebook_id']= $user_info[0]['facebook_id'];
							$post['google_id']	= $user_info[0]['google_id'];
							$post['twitter_id']	= $user_info[0]['twitter_id'];
							$post['first_name']	= $user_info[0]['first_name'];
							$post['last_name']	= $user_info[0]['last_name'];
							$post['dob']		= $user_info[0]['dob'];
							$post['email_id']	= $user_info[0]['email_id'];
							$post['phone_number']=$user_info[0]['mobile'];
							if($user_info[0]['image']!='')
							{
								$post['image']		= $this->user_model->get_user_img_url('user_image',$user_info[0]['image']);	
							}
							else 
							{
								$post['image']		= "";	
							}
							
							$post['address1']	= $user_info[0]['address1'];
							$post['address2']	= $user_info[0]['address2'];
							$post['state']		= $user_info[0]['state'];
							$post['city']		= $user_info[0]['city'];
							$post['pin']		= $user_info[0]['pin'];
							$post['gender']		= $user_info[0]['gender'];
							
						}
					}
				}
				else 
				{
					//This number already registered
					$post['message']="mobile number already registered";
				}
			}
			else 
			{
				//email id already registered
				$post['message']="email id already registered";
			}	
			$this -> response($post);
	}
	public function userLogoutView_get()
	{
		//This view is for getting user logout
		$this->load->view('webservices/user_logout');
	}
	public function userLogout_post()
	{

		//This function is for getting user logout 
		//now getting the value from view
		$user_id=$this->input->post('user_id');
		 
		//now user is logout in data base
		$data=array('device_token'=>'');
		$result=$this->user_model->updateRecord('user',$data,array('user_id'=>$user_id));
		//echo $this->db->last_query();die;
		if($result==0)
		{
			$post['message']="failure";	
		} 
		else 
		{
			$post['message']="success";
		}
		echo json_encode($post);
		//$this->response($post);
	}
	public function forgotPasswordView_get()
	{
		//This view is for forgot password
		$this->load->view('webservices/forgot_password');
	}
	public function forgotPassword_post()
	{
		//This function is for forgot password
		//Now getting the value from view
		$email_id=$this->input->post('email_id');
		//now getting the password for this email_id
		$result=$this->user_model->getRecord('user',array('email_id'=>$email_id));
		if($result==0)
		{
			//invalid email_id
			$post['message']='failure';
		}
		else 
		{
			//email_id is valid
			//Now check for the password if user register with social
			if($result[0]['password']=='')
			{
				//user registerd with social
				$subject="Forgot Password";
				$dec_password='<br> You are login with social network';
				$this->user_model->sendEmail($email_id,$dec_password,$subject);
				$post['message']='success';	
			}
			else 
			{
				//now send the email containing the password
				$dec_password=$this->user_model->encrypt_decrypt('decrypt', $result[0]['password']);
				$subject="Forgot Password";
				$this->user_model->sendEmail($email_id,$dec_password,$subject);
				$post['message']='success';
			}	
		}
		echo json_encode($post);
	}
	public function changePasswordView_get()
	{
		//This view is for change password
		$this->load->view('webservices/change_password');
	}
	public function changePassword_post()
	{
		//This function is for changing the password
		//Now getting the value from view
		$user_id=$this->input->post('user_id');
		$old_password=$this->input->post('old_password');
		$new_password=$this->input->post('new_password');
		//Now checking the authentication for this user
		//encrypt the password
		$enc_password=$this->user_model->encrypt_decrypt('encrypt', $old_password);
		
		$new_password=$this->user_model->encrypt_decrypt('encrypt', $new_password);

		$data=array('user_id'=>$user_id,'password'=>$enc_password);
		$check_result=$this->user_model->getRecord('user',$data);
		if($check_result==0)
		{
			//wrong credential 
			$post['message']="failure";
		}
		else 
		{
			//credential match
			//Now update the password
			$result=$this->user_model->updateRecord('user',array('password'=>$new_password),array('user_id'=>$user_id));
			if($result==0)
			{
				$post['message']="failure";
			}
			else 
			{
				$post['message']="success";	
			}	
		}
		echo json_encode($post);
	}
	public function getAllMedicineView_get()
	{
		//Thisview is for getting all the medicine
		$this->load->view('webservices/get_all_medicine');
	}
	public function getAllMedicine_post()
	{
		//This funtion is for geting all the active medicine
		$user_id=$this->input->post('user_id');
		//now getting all the medicine for this user
		$result=$this->user_model->getRecord('medicine',array('user_id'=>$user_id,'medicine_status'=>0,'end_date >='=>date('Y-m-d')));
		//echo $this->db->last_query();die;
		if($result==0)
		{
			//there is no medicine for this user	
			$post['message']="failure";
		}
		else 
		{
			//there  are some medicine for this user
			$post['message']="success";
			
			foreach ($result as $value) 
			{
				$p['medicine_id']=$value['medicine_id'];
				$p['user_id']=$value['user_id'];
				$p['name']=$value['name'];
				$p['dosage_time']=$value['dosage_time'];
				$p['food']=$value['food'];
				$p['medicine_form']=$value['medicine_form'];
				$p['dosage_quantity']=$value['dosage_quantity'];
				$p['start_date']=$value['start_date'];
				$p['end_date']=$value['end_date'];
				$p['quantity_avail']=$value['quantity_avail'];
				$p['picture']=$this->user_model->get_user_img_url('uploads',$value['picture']);
				$p['notes']=$value['notes'];
				$p['medicine_status']=$value['medicine_status'];
				$p['segment']=$value['segment'];
				//now get the count of skip medicine 
				$p['skip']=$this->user_model->getRecordCount('medicine_status',array('medicine_id'=>$value['medicine_id'],'status'=>1));	
				//now get the count of taken medicine
				$p['taken']=$this->user_model->getRecordCount('medicine_status',array('medicine_id'=>$value['medicine_id'],'status'=>0));
				$p['created']=$value['created'];
				$p['updated']=$value['updated'];
				$post['medicine'][]=$p;	
			}
			 
		}
		echo json_encode($post);
	}
	public function getMedicationHistoryView_get()
	{
		$this->load->view('webservices/medicine_history');
	}
	public function getMedicationHistory_post()
	{
		//This funtion is for geting all the active medicine
		$user_id=$this->input->post('user_id');
		//now getting all the medicine for this user
		$date=date('Y-m-d');
		$where="(`user_id`= '$user_id' AND `end_date`< '$date') OR (`user_id`= '$user_id'  AND `medicine_status`='1') ";
		$result=$this->user_model->getRecord('medicine',$where);
		//$result=$this->user_model->getRecordOr('medicine',array('user_id'=>$user_id),array('medicine_status'=>1,'end_date <'=>date('Y-m-d')));
		//$result=$this->user_model->getRecordOr('medicine',array('user_id'=>$user_id,'end_date <'=>date('Y-m-d')),array('medicine_status'=>1));
		
		//$result=$this->user_model->getRecordQuery('user_id','medicine_status','end_date',$user_id,1,date('Y-m-d'),'medicine');
		//echo $this->db->last_query();die;
		if($result==0)
		{
			//there is no medicine for this user	
			$post['message']="failure";
		}
		else 
		{
			//there  are some medicine for this user
			$post['message']="success";
			
			foreach ($result as $value) 
			{
				$p['medicine_id']=$value['medicine_id'];
				$p['user_id']=$value['user_id'];
				$p['name']=$value['name'];
				$p['dosage_time']=$value['dosage_time'];
				$p['food']=$value['food'];
				$p['medicine_form']=$value['medicine_form'];
				$p['dosage_quantity']=$value['dosage_quantity'];
				$p['start_date']=$value['start_date'];
				$p['end_date']=$value['end_date'];
				$p['quantity_avail']=$value['quantity_avail'];
				$p['picture']=$this->user_model->get_user_img_url('uploads',$value['picture']);
				$p['notes']=$value['notes'];
				$p['medicine_status']=$value['medicine_status'];
				$p['segment']=$value['segment'];
				//now get the count of skip medicine 
				$p['skip']=$this->user_model->getRecordCount('medicine_status',array('medicine_id'=>$value['medicine_id'],'status'=>1));	
				//now get the count of taken medicine
				$p['taken']=$this->user_model->getRecordCount('medicine_status',array('medicine_id'=>$value['medicine_id'],'status'=>0));
				$post['medicine'][]=$p;	
			}
			 
		}
		echo json_encode($post);
	}
	public function getAllCaregiverView_get()
	{
		//This view is for getting all caregiver
		$this->load->view('webservices/view_caregiver');
	}
	public function getAllCaregiver_post()
	{
		//This function is for getting all caregiver
		$user_id=$this->input->post('user_id');
		//now get the caregiver of this user
		$result=$this->user_model->getRecord('caregiver',array('user_id'=>$user_id));
		
		if($result==0)
		{
			//there is no caregiver	
			$post['message']="failure";
		}
		else 
		{
			//there are some caregiver
			$post['message']="success";
			foreach ($result as $value) 
			{
				$po['care_id']		= $value['care_id'];
				$po['user_id']		= $value['user_id'];
				$po['c_user_id']	= $value['c_user_id'];
				//now get the detail of caregiver
				$c_result=$this->user_model->getRecord('user',array('user_id'=>$value['c_user_id']));
				$po['c_first_name']	= $c_result[0]['first_name'];
				$po['c_last_name']	= $c_result[0]['last_name'];
				$po['c_email_id']	= $c_result[0]['email_id'];
				$po['c_mobile_number']=$c_result[0]['mobile'];
				
				$po['permission']	= $value['permission'];
				$po['updated']		= $value['updated'];
				$po['created']		= $value['created'];
				$po['care_id']		= $value['care_id'];
				$post['caregiver'][]=$po;
			}			
		}
		echo json_encode($post);
	}
	public function getMyBuddiesView_get()
	{
		//This view is for getting the my buddies
		$this->load->view('webservices/view_buddies');
	}
	public function getMyBuddies_post()
	{
		//This function is for getting all the buddies of the user
		//now getting the value from view
		 $user_id=$this->input->post('user_id');
		 //now getting the value from caregiver table
		 $result=$this->user_model->getRecord('caregiver',array('c_user_id'=>$user_id));
		 if($result==0)
		 {
		 	//There is no buddies
		 	$post['message']="failure";
		 }
		 else 
		 {
			 //There are some buddies
			 $post['message']="success";
			 foreach ($result as $value) 
			 {
				$po['care_id']		= $value['care_id'];
				$po['b_user_id']	= $value['user_id'];
				$po['user_id']		= $value['c_user_id'];
				//now get the detail of caregiver
				$c_result=$this->user_model->getRecord('user',array('user_id'=>$value['user_id']));
				$po['b_first_name']	= $c_result[0]['first_name'];
				$po['b_last_name']	= $c_result[0]['last_name'];
				$po['b_email_id']	= $c_result[0]['email_id'];
				$po['b_mobile_number']=$c_result[0]['mobile'];
				
				$po['permission']	= $value['permission'];
				$po['updated']		= $value['updated'];
				$po['created']		= $value['created'];
				$po['care_id']		= $value['care_id'];
				$post['caregiver'][]=$po;
			 }
		 }
		 echo json_encode($post);
	}
	public function deleteCaregiverView_get()
	{
		//this view is for delete the caregiver 
		$this->load->view('webservices/delete_caregiver');
	}
	public function deleteCaregiver_post()
	{
		//this function is for delete caregiver according to the care_id
		//now getting the value from view
		$care_id=$this->input->post('care_id');
		$result=$this->user_model->deleteRecord(array('care_id'=>$care_id),'caregiver');
		if($result==0)
		{
			$post['message']="failure";	
		} 
		else 
		{
			$post['message']="success";
		}
		echo json_encode($post);
	}
	public function updateCareiverView_get()
	{
		//this view is for update a caregiver permission
		$this->load->view('webservices/update_caregiver');
	}
	public function updateCaregiver_post()
	{
		//This function is for update caregiver
		//Now getting the value from view
		$care_id=$this->input->post('care_id');
		$permission=$this->input->post('permission');
		//Now update the permission
		$result=$this->user_model->updateRecord('caregiver',array('permission'=>$permission),array('care_id'=>$care_id));
		if($result==0)
		{
			$post['message']="failure";
		}
		else 
		{
			$post['message']="success";
		}
		echo json_encode($post);
	}
	public function stopMedicineView_get()
	{
		//This view is for stop the medication
		$this->load->view('webservices/stop_medicine');
	}
	public function stopMedicine_post()
	{
		//This function is for the stop the current mediciaton 
		//now getting the value from view
		$medicine_id=$this->input->post('medicine_id');
		//now stop this medicine by update the status of 1
		$result=$this->user_model->updateRecord('medicine',array('medicine_status'=>1),array('medicine_id'=>$medicine_id));
		if($result==0)
		{
			//medicine not stopped
			$post['message']="failure";	
		}
		else 
		{
			//medicine stopped
			$post['message']="success";
		}
		echo json_encode($post);
		
	}
	public function addMedicineStatusView_get()
	{
		//This view is for update the medication status of skipped or taken
		$this->load->view('webservices/add_medicine_status');
	}
	public function addMedicineStatus_post()
	{
		//This fucntion is for update the medicine status
		//now getting the value from view
		$user_id=$this->input->post('user_id');
		$medicine_id=$this->input->post('medicine_id');
		$status=$this->input->post('status');
		$created=$this->input->post('created');
		//now update the medicine_status table
		$data=array('user_id'=>$user_id,'medicine_id'=>$medicine_id,'status'=>$status,'created'=>$created);
		$result=$this->user_model->saveRecord('medicine_status',$data);
		if($result==0)
		{
			//medicine status not updated
			$post['message']="failure";	
		}
		else 
		{
			//medicine Status updated
			//now change the available quantity of medicine according to the status
			if($status==0)
			{
				//this is for medicine taken
				//now reduse the medicine available
				$medicine_result=$this->user_model->getRecord('medicine',array('medicine_id'=>$medicine_id));
				if($medicine_result!=0)
				{
					//now check the medicine available non zero value
					if($medicine_result[0]['quantity_avail']>$medicine_result[0]['dosage_quantity'])
					{
						$medicine_avail=$medicine_result[0]['quantity_avail']-$medicine_result[0]['dosage_quantity'];
						//now udpate the medicine avail
						$this->user_model->updateRecord('medicine',array('quantity_avail'=>$medicine_avail),array('medicine_id'=>$medicine_id));
					}
				}
			}
			$post['message']="success";
		}
		echo json_encode($post);
	}
	public function getMedicineStatusView_get()
	{
		//This view is for getting the medicine status
		$this->load->view('webservices/get_medicine_status');
	}
	public function getMedicineStatus_post()
	{
		//This function is for getting the medicine status
		//now getting the values from view
		$user_id=$this->input->post('user_id');
		$medicine_id=$this->input->post('medicine_id');
		//now get the record for this credential
		$data=array('user_id'=>$user_id,'medicine_id'=>$medicine_id);
		$result=$this->user_model->getRecord('medicine_status',$data);
		if($result==0)
		{
			//there is no data for this credential
			$post['message']='failure';
		}
		else 
		{
			//there are some data for this credential
			$post['message']='successs';
			foreach ($result as $value) 
			{
				$medicin['medicine_status_id']=$value['medicine_status_id'];
				$medicin['user_id']=$value['user_id'];
				$medicin['medicine_id']=$value['medicine_id'];
				$medicin['status']=$value['status'];
				$medicin['created']=$value['created'];
				$post['medicine_status'][]=$medicin;
			}
			
		}
		echo json_encode($post);
	}
	public function sendFeedBackView_get()
	{
		//This view is for sending the feedback
		$this->load->view('webservices/send_feedback');
	}
	public function sendFeedBack_post()
	{
		//This function is for sending the feedback
		//now getting the value from view
		$mobile_number=$this->input->post('mobile_number');
		$name=$this->input->post('name');
		$email_id=$this->input->post('email_id');
		$message=$this->input->post('message');
		$subject="Feedback Email From Mediware";
		//now sending the email
		$result=$this->user_model->sendFeedbackEmail($name,$email_id,$mobile_number,$message,$subject);
		
		if($result==1)
		{
			$post['message']='success';	
		}
		else 
		{
			$post['message']='failure';
		}
		echo json_encode($post);
	}
}
?>
