<?php

return
    array(
        'users' => array(
            'role'        => 'role_id',
            'files'       => 'image_id',
        ),
        'clients' => array(
            'client_status'     => 'client_status_id',
            'files'             => 'image_id',
        ),
        'pets' => array(
            'files'         => 'image_id',
            'pet_color'     => 'pet_color_id',
            'pet_size'      => 'pet_size_id',
            'pet_sex'       => 'pet_sex_id',
            'pet_breed'     => 'pet_breed_id',
            'pet_type'      => 'pet_type_id',
            'pet_status'    => 'pet_status_id',
        ),
        'pet_breed' => array(
            'pet_type'    => 'pet_type_id'
        ),
        'pet_vet' => array(
            'pets'  => 'pet_id',
            'vets'  => 'vet_id'
        ),
        'r_boarding' => array(
            'clients'                   => 'client_id',
            'pets'                      => 'pet_id',
            'stay_reasons'              => 'stay_reason_id',
            'boarding_centers'          => 'boarding_center_id',
            'reports'                   => 'report_id'
        ),
        'r_boarding_ss' => array(
            'r_boarding'            => 'r_boarding_id',
            'schedule_services'     => 'ss_id'
        ),
        'r_boarding_meds' => array(
            'r_boarding'        => 'r_boarding_id',
            'meds'              => 'med_id',
            'meds_type'         => 'med_type_id',
            'md_dosage'         => 'med_dosage_id',
            'md_dose_type'      => 'med_dose_type_id'
        ),
        'r_boarding_diets' => array(
            'r_boarding'        => 'r_boarding_id',
            'diets'             => 'diet_id',
            'diets_type'        => 'diet_type_id',
            'md_dosage'         => 'diet_dosage_id',
            'md_dose_type'      => 'diet_dose_type_id'
        ),
        'r_daycare' => array(),
        'r_training' => array(
            'training_centers'    => 'training_center_id'
        ),
        'r_grooms' => array(
            'grooms_centers'    => 'grooms_center_id'
        ),
        'cp'  => array(
            'clients' => 'client_id',
            'pets'    => 'pet_id'
        ),
        'payments' => array(
            'payment_status'  => 'payment_status_id',
            'reports'         => 'report_id'
        ),
        'clients_survey' => array(
            'clients' => 'reffered_client_id',
            'client_survey_findout' => 'client_survey_findout_id',
            'client_survey_income' => 'client_survey_income_id'
        ),
        'groomers' => array(
            'files' => 'image_id'
        ),
        'trainers' => array(
            'files' => 'image_id'
        ),
        'training_schedule' => array(
            'training_groups' => 'tg_id',
            'trainers'        => 't_id'
        )
    );