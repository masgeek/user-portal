<?php

function bravepop_LearnDash_filter_match($rules){
   $learnDashMatch = true; $userID = get_current_user_id();
   foreach ($rules as $key => $LDRule) {
      if($learnDashMatch  && !empty($LDRule->id) ){
         //Lesson Match
         if(function_exists('learndash_is_lesson_complete') && $LDRule->action === 'completed_lesson'){
            if(learndash_is_lesson_complete( $userID, absint($LDRule->id)) === false ){   $learnDashMatch = false;  }
         }
         if(function_exists('learndash_is_lesson_complete') && $LDRule->action === 'not_completed_lesson'){
            if(learndash_is_lesson_complete( $userID, absint($LDRule->id)) === true ){   $learnDashMatch = false;  }
         }
         //Course Match
         if(function_exists('learndash_course_completed') && $LDRule->action === 'completed_course'){
            if(learndash_course_completed( $userID, absint($LDRule->id)) === false ){   $learnDashMatch = false;  }
         }
         if(function_exists('learndash_course_completed') && $LDRule->action === 'not_completed_course'){
            if(learndash_course_completed( $userID, absint($LDRule->id)) === true ){   $learnDashMatch = false;  }
         }
         //Quiz Match
         if(function_exists('learndash_quiz_completed') && $LDRule->action === 'completed_quiz'){
            if(learndash_quiz_completed( $userID, absint($LDRule->id)) === false ){   $learnDashMatch = false;  }
         }
         if(function_exists('learndash_quiz_completed') && $LDRule->action === 'not_completed_quiz'){
            if(learndash_quiz_completed( $userID, absint($LDRule->id)) === true ){   $learnDashMatch = false;  }
         }
         // if(function_exists('learndash_course_progress')){
         //    $course_completion = learndash_course_progress( array('course_id'=> 1247, 'user_id'=>null, 'array'=> true ) );
         //    error_log('learndash_course_progress!'. wp_json_encode($course_completion));
         //    {"percentage":75,"completed":3,"total":4}
         // }
      }
   }

   return $learnDashMatch;

}

function bravepop_rest_learnDash_objects($addedItemIDs){
   $LDObjects = new stdClass();
   $allCourses = []; $allLessons = []; $allQuizzes = [];

   $courses = get_posts(array( 'post_type' => 'sfwd-courses', 'numberposts' => -1 ));
   foreach ( $courses as $course ) {
         $object = new stdClass();
         $object->ID = $course->ID ;
         $object->title = $course->post_title ;
         $object->link = esc_url(get_permalink( $course->ID )) ;
         $object->slug = $course->post_name ;
         if(!in_array($course->ID, $addedItemIDs)){
            $allCourses[] = $object;
            $addedItemIDs[] = $course->ID;
         }
   }
   $lessons = get_posts(array( 'post_type' => 'sfwd-lessons', 'numberposts' => -1 ));
   foreach ( $lessons as $lesson ) {
         $object = new stdClass();
         $object->ID = $lesson->ID ;
         $object->title = $lesson->post_title ;
         $object->link = esc_url(get_permalink( $lesson->ID )) ;
         $object->slug = $lesson->post_name ;
         if(!in_array($lesson->ID, $addedItemIDs)){
            $allLessons[] = $object;
            $addedItemIDs[] = $lesson->ID;
         }
   }
   $quizzes = get_posts(array( 'post_type' => 'sfwd-quiz', 'numberposts' => -1 ));
   foreach ( $quizzes as $quiz ) {
         $object = new stdClass();
         $object->ID = $quiz->ID ;
         $object->title = $quiz->post_title ;
         $object->link = esc_url(get_permalink( $quiz->ID )) ;
         $object->slug = $quiz->post_name ;
         if(!in_array($quiz->ID, $addedItemIDs)){
            $allQuizzes[] = $object;
            $addedItemIDs[] = $quiz->ID;
         }
   }

   $LDObjects->courses = $allCourses;
   $LDObjects->lessons = $allLessons;
   $LDObjects->quizzes = $allQuizzes;
   
   return $LDObjects;
}