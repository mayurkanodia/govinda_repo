<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*class Home extends CI_Controller{*/

include (APPPATH . 'libraries/REST_Controller.php');

class Medication extends REST_Controller {

	public function __construct() 
	{
		parent::__construct();
		$this -> load -> model('user_model');
	}
	public function addMedicineView_get()
	{
		//This view is for adding the medicine
		$this->load->view('webservices/add_medicine');
	}
	public function addMedicine_post()
	{
		//This function is for add medicine
		//now getting the value from view
		
		$user_id		= $this->input->post('user_id');
		$medicine_id	= $this->input->post('medicine_id');
		$name			= $this->input->post('name');
		$dosage_time	= $this->input->post('dosage_time');
		$food			= $this->input->post('food');
		$medicine_form	= $this->input->post('medicine_form');
		$dosage_quantity= $this->input->post('dosage_quantity');
		$start_date		= $this->input->post('start_date');
		$end_date		= $this->input->post('end_date');
		$segment		= $this->input->post('segment');
		$quantity_avail = $this->input->post('quantity_avail');
		//now check the medicine
		if($medicine_id=='')
		{
			//now check this medicine neame is already added or not
			$medi_result=$this->user_model->getRecord('medicine',array('user_id'=> $user_id,'name'=> $name));
			if($medi_result==0)
			{
				//there is no medicine in database
				if($_FILES)
				{
					if($_FILES['picture']['name']!='')
					{
						$imanename = $_FILES['picture']['name'];
			
						
						$temp = explode(".", $_FILES["picture"]["name"]);
						$newfilename = rand(1, 99999) . '.' . end($temp);
						//$newfilename;
						$path= './uploads/'.$newfilename;
						$upload = copy($_FILES['picture']['tmp_name'], $path);
					}
					else 
					{
						$newfilename="";
					}
						
				}
				else {
					$newfilename="";
				}
				//now insert the medicine in medicine table
				$data=array(
							'user_id'		=> $user_id,
							'name'			=> $name,
							'dosage_time'	=> $dosage_time,
							'food'			=> $food,
							'medicine_form'	=> $medicine_form,
							'dosage_quantity'=>$dosage_quantity,
							'start_date' 	=> $start_date,
							'end_date'	 	=> $end_date,
							'picture'		=> $newfilename,
							'segment'		=> $segment,
							'quantity_avail'=> $quantity_avail,
							'created'		=> date('Y-m-d H:I:s'));
							
				$result=$this->user_model->saveRecord('medicine',$data);
				if($result==0)
				{
					$post['message']="failure";
				}
				else 
				{
					$post['message']="success";
					$post['medicine_id']=$result;
					$post['medicine_image']=$this->user_model->get_user_img_url('uploads',$newfilename);
					
				}
			}
			else 
			{
				$post['message']="medicine already added";	
			}
		}
		else 
		{
			//it is already added in database
			//Now getting the medicine detail
			$medicine_detail=$this->user_model->getRecord('medicine',array('medicine_id'=>$medicine_id));
			$image_medicine=$medicine_detail[0]['picture'];
			//now update the medicine according to the medicine_id
			if($_FILES)
			{
				if($_FILES['picture']['name']!='')
				{
					$imanename = $_FILES['picture']['name'];
		
					
					$temp = explode(".", $_FILES["picture"]["name"]);
					$newfilename = rand(1, 99999) . '.' . end($temp);
					$newfilename;
					$path= './uploads/'.$newfilename;
					$upload = copy($_FILES['picture']['tmp_name'], $path);
				}
				else 
				{
					$newfilename=$image_medicine;
				}
			}
			else {
				$newfilename=$image_medicine;
			}
			
			$data=array(
						'name'				=> $name,
						'dosage_time'		=> $dosage_time,
						'food'				=> $food,
						'medicine_form'		=> $medicine_form,
						'dosage_quantity'	=> $dosage_quantity,
						'start_date' 		=> $start_date,
						'end_date'	 		=> $end_date,
						'picture'			=> $newfilename,
						'segment'			=> $segment,
						'quantity_avail'	=> $quantity_avail,
						'created'			=> date('Y-m-d H:I:s'),
						'updated'			=> date('Y-m-d H:I:s'));
						
			$where=array(
						'user_id'	=> $user_id,
						'medicine_id'=>$medicine_id);			
			$result=$this->user_model->updateRecord('medicine',$data,$where);
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
	public function addCaregiverView_get()
	{
		//This view is for add caregiver
		$this->load->view('webservices/add_caregiver');	
	}
	public function addCaregiver_post()
	{
		//This function is for adding the caregiver
		//now getting the values from view
		$user_id=$this->input->post('user_id');
		$mobile=$this->input->post('mobile');
		$permission=$this->input->post('permission');
		//now check the mobile number present in user table
		$result=$this->user_model->getRecord('user',array('mobile'=>$mobile));
		if($result==0)
		{
			//this user not present in the app
			$post['message']="user not present";	
		}
		else 
		{
			//This user present in the app
			//now check this user already a caregiver for this user
			$care_result=$this->user_model->getRecord('caregiver',array('user_id'=>$user_id,'c_user_id'=>$result[0]['user_id']));
			if($care_result==0)
			{
				//this combination not present in the database
				//now add the caregiver for this user
				$data=array(
							'user_id'=>$user_id,
							'c_user_id'=>$result[0]['user_id'],
							'permission'=>$permission,
							'created'=>date('Y-m-d H:I:s')
							);
				$care=$this->user_model->saveRecord('caregiver',$data);
				if($care==0)
				{
					//server error
					$post['message']="failure";
				}
				else 
				{
					//caregiver added
					$post['message']="success";
					$post['care_id']=$care;
					$post['c_first_name']=$result[0]['first_name'];
					$post['c_last_name']=$result[0]['last_name'];
					$post['c_email_id']=$result[0]['email_id'];
					
				}	
			}
			else 
			{
				//This combsination is present in the database
				$post['message']="already caregiver";
			}
		}
		echo json_encode($post);	
	}
		public function updateUserView_get()
	{
		//This view is for update the user profile
		$this->load->view('webservices/update_user');
	}
	public function updateUser_post()
	{
		//This function is for updating the user profile
		//now get the information from view
		$user_id		= $this->input->post('user_id');
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
		$dob			= $this->input->post('dob');
		$device_token	= $this->input->post('device_token');
		//$password		= $this->input->post('password');
		
		//now getting the value from view
		$user_info=$this->user_model->getRecord('user',array('user_id'=>$user_id));
		if($_FILES)
		{
			if($_FILES['profile_image']['name']==$user_info[0]['image'])
			{
				//updated image is same as old image
				$newfilename=$user_info[0]['image'];
			}
			else 
			{
				//both images are different
				if($_FILES['profile_image']['name']!='')
				{
					//Rename a image file
					$imanename = $_FILES['profile_image']['name'];
		
					
					$temp = explode(".", $_FILES["profile_image"]["name"]);
					$newfilename = rand(1, 99999) . '.' . end($temp);
					$newfilename;
					//Now upload the image
					
					$path= './user_image/'.$newfilename;
					$upload = copy($_FILES['profile_image']['tmp_name'], $path);
				}
				else 
				{
					$newfilename=$user_info[0]['image'];
				}	
			}
		}
		else 
		{
			$newfilename=$user_info[0]['image'];
		}
		//binding up the data for update
		$data=array(
					'first_name'	=> $first_name,
					'last_name'		=> $last_name,
					'email_id'		=> $email_id,
					'dob'			=> $dob,
					'image'			=> $newfilename,
					'mobile'		=> $phone_number,
					'address1'		=> $address1,
					'address2'		=> $address2,
					'state'			=> $state,
					'city'			=> $city,
					'pin'			=> $pin,
					'gender'		=> $gender,
					'device_token'	=> $device_token,
					'created'		=> date('Y-m-d H:I:s'));	
		//Now update the user data according to user id
		
		$result=$this->user_model->updateRecord('user',$data,array('user_id'=>$user_id));
		if($result!=0)
		{
			//data updated
			//get the updated user information 
			$user_update=$this->user_model->getRecord('user',array('user_id'=>$user_id));
			
			$post['message']	= "success";
			$post['user_id']	= $user_update[0]['user_id'];
			$post['first_name']	= $user_update[0]['first_name'];
			$post['last_name']	= $user_update[0]['last_name'];
			$post['dob']		= $user_update[0]['dob'];
			$post['email_id']	= $user_update[0]['email_id'];
			$post['phone_number']=$user_update[0]['mobile'];
			$post['image']		= $this->user_model->get_user_img_url('user_image',$user_update[0]['image']);
			$post['address1']	= $user_update[0]['address1'];
			$post['address2']	= $user_update[0]['address2'];
			$post['state']		= $user_update[0]['state'];
			$post['city']		= $user_update[0]['city'];
			$post['pin']		= $user_update[0]['pin'];
			$post['gender']		= $user_update[0]['gender'];
		}
		else 
		{
			//not updated
			$post['message']	= "not update this time";
		}				
		$this -> response($post);
	}
	public function updateBuddyMedicineView_get()
	{
		//This view is for update the buddy medicine
		$this->load->view('webservices/update_buddy_medicine');	
	}
	public function updateBuddyMedicine_post()
	{
		
		//This function is for update medicine
		//now getting the value from view
		
		//$user_id		= $this->input->post('user_id');
		$medicine_id	= $this->input->post('medicine_id');
		$name			= $this->input->post('name');
		$dosage_time	= $this->input->post('dosage_time');
		$food			= $this->input->post('food');
		$medicine_form	= $this->input->post('medicine_form');
		$dosage_quantity= $this->input->post('dosage_quantity');
		$start_date		= $this->input->post('start_date');
		$end_date		= $this->input->post('end_date');
		
		
		//now check the medicine
		if($medicine_id=='')
		{
			//there is no medicine in database
			if($_FILES)
			{
				if($_FILES['picture']['name']!='')
				{
					
					$imanename = $_FILES['picture']['name'];
		
					
					$temp = explode(".", $_FILES["picture"]["name"]);
					$newfilename = rand(1, 99999) . '.' . end($temp);
					$newfilename;
					$path= './uploads/'.$newfilename;
					$upload = copy($_FILES['picture']['tmp_name'], $path);
				}
				else 
				{
					$newfilename="";
				}
			}
			else {
				$newfilename='';
			}
			
			
			//now insert the medicine in medicine table
			$data=array(
						//'user_id'	=> $user_id,
						'name'		=> $name,
						'dosage_time'=>$dosage_time,
						'food'		=> $food,
						'medicine_form'=>$medicine_form,
						'dosage_quantity'=>$dosage_quantity,
						'start_date' => $start_date,
						'end_date'	 => $end_date,
						'picture'	=> $newfilename	);
						
			$result=$this->user_model->saveRecord('medicine',$data);
			if($result==0)
			{
				$post['message']="failure";
			}
			else 
			{
				$post['message']="success";
				$post['medicine_id']=$result;
				$post['medicine_image']=$this->user_model->get_user_img_url('uploads',$newfilename);
				
			}
			
		}
		else 
		{
			//Now getting the medicine detail
			$medicine_detail=$this->user_model->getRecord('medicine',array('medicine_id'=>$medicine_id));
			$image_medicine=$medicine_detail[0]['picture'];
			
			//it is already added in database
			//now update the medicine according to the medicine_id
			if($_FILES)
			{
				if($_FILES['picture']['name']!='')
				{
					$imanename = $_FILES['picture']['name'];
		
					
					$temp = explode(".", $_FILES["picture"]["name"]);
					$newfilename = rand(1, 99999) . '.' . end($temp);
					$newfilename;
					$path= './uploads/'.$newfilename;
					$upload = copy($_FILES['picture']['tmp_name'], $path);
				}
				else 
				{
					$newfilename=$image_medicine;
				}
			}
			else {
				$newfilename=$image_medicine;
			}
			
			$data=array(
						'name'		=> $name,
						'dosage_time'=>$dosage_time,
						'food'		=> $food,
						'medicine_form'=>$medicine_form,
						'dosage_quantity'=>$dosage_quantity,
						'start_date' => $start_date,
						'end_date'	 => $end_date,
						'picture'	=> $newfilename);
			$where=array(
						//'user_id'	=> $user_id,
						'medicine_id'=>$medicine_id);			
			$result=$this->user_model->updateRecord('medicine',$data,$where);
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
}
	
?>