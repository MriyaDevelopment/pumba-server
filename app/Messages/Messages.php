<?php

namespace App\Messages;

class Messages {

    const allFieldsError = "All fields are mandatory";

    //Profile
    const profileEditedSuccess = "Profile edited successfully";
    const profileDeleteSuccess = "User account deleted successfully";
    const profileError = "Profile does not exist";
    const profileFiltersAddSuccess = "Filters added successfully";

    //Auth
    const userError = "User does not exist";
    const userPasswordError = "Wrong password";
    const userRegisterEmailValidator = "Mail already exist";
    const userRegisterNameValidator = "Name already exists";
    const userRegisterSuccess = "Registration completed successfully";
    const userChangePasswordSuccess = "Password changed successfully";
    const userChangeMailSuccess = "Email changed successfully";

    //Child
    const childError = "Child does not exist";
    const childEditedSuccess = "Child edited successfully";
    const childDeleteSuccess = "Child deleted successfully";
    const childAddedSuccess = "Child added successfully";

    //Guide
    const guidesError = "Guide does not exist";

    //Reminder
    const reminderAddedSuccess = "Reminder added successfully";
    const reminderDeleteSuccess = "Reminder delete successfully";
    const reminderEditError = "Reminder {id} error";
    const reminderEditSuccess = "Reminder edited successfully";

    //Memories
    const memoryAddedSuccess = "Memory added successfully";
    const memoryDeleteSuccess = "Memory delete successfully";
    const memoryEditedSuccess = "Memory edited successfully";

    //Mailer
    const mailSearchError = "Mail does not exist";
    const codeError = "No such code exists";
    const codeSuccess = "Code is correct";

    //FCM
    const fcmUpdatedSuccess = "fcm_token updated success";

    //Alert
    const alertSendSuccess = "Bot sent a message";
}



