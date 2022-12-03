<?php

namespace App\Messages;

class Messages {

    const allFieldsError = "All fields are mandatory";

    //Edit
    const profileEditedSuccess = "Profile edited successfully";
    const profileDeleteSuccess = "User account deleted successfully";
    const profileError = "Profile does not exist";

    //Auth
    const userError = "User does not exist";
    const userPasswordError = "Wrong password";
    const userRegisterEmailValidator = "Mail already exist";
    const userRegisterNameValidator = "Name already exists";
    const userRegisterSuccess = "Registration completed successfully";

    //Child
    const childError = "Child does not exist";
    const childEditedSuccess = "Child edited successfully";
    const childDeleteSuccess = "Child deleted successfully";
    const childAddedSuccess = "Child added successfully";
}



