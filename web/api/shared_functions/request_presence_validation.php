 <?php 
 function presenceValidation($request, $required_fields){
     $errors = Array();
     $request_fields = Array();
     foreach($required_fields as $field){
         if(isset($request[$field]) && $request[$field]!=''){
             array_push($errors,Array("field"=>$field, "message" => "is required"));
         } else {
             $request_fields[$field] = $request[$field];
         }
     }
     return Array($request_fields, $errors);
 }
 ?>