<?php

    class InternetData extends ApiAccess{
        

         //Purchase Data
		public function purchaseData($body,$networkDetails,$datagroup,$actualPlanId){

			$response = array();
            $details=$this->model->getApiDetails();

            //Check Data Group Type
            if($datagroup == "SME"){$name="Sme"; $thenetworkId=$networkDetails["smeId"];} 
            elseif($datagroup == "Gifting"){$name="Gifting"; $thenetworkId=$networkDetails["giftingId"];} 
            else {$name ="Corporate"; $thenetworkId=$networkDetails["corporateId"]; }

            //Get Api Key Details
            $networkname = strtolower($networkDetails["network"]);
            $host = self::getConfigValue($details,$networkname.$name."Provider");
            $apiKey = self::getConfigValue($details,$networkname.$name."Api");

            //Check If API Is Is Using N3TData Or Bilalsubs
            if(strpos($host, 'n3tdata') !== false){
                $hostuserurl="https://n3tdata.com/api/user/";
                return $this->purchaseDataWithBasicAuthentication($body,$host,$hostuserurl,$apiKey,$thenetworkId,$actualPlanId);
            }

            if(strpos($host, 'bilalsadasub') !== false){
                $hostuserurl="https://bilalsadasub.com/api/user/";
                return $this->purchaseDataWithBasicAuthentication($body,$host,$hostuserurl,$apiKey,$thenetworkId,$actualPlanId);
            }
            
               if(strpos($host, 'smeplug') !== false){
                $hostuserurl="https://smeplug.com/api/v1/";
                return $this->purchaseDataWithBearerAuthentication($body,$host,$hostuserurl,$apiKey,$thenetworkId,$actualPlanId);
            }
            
                if(strpos($host, 'autopilotng') !== false){
                $hostuserurl="https://autopilotng.com/api/live";
                return $this->purchaseDataWithAutopilot($body,$host,$hostuserurl,$apiKey,$thenetworkId,$actualPlanId);
            }
            // ------------------------------------------
            //  Purchase Data
            // ------------------------------------------
            
            if($body->ported_number == "false"){$ported_number="false";} else{$ported_number="true";}

            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "network": "'.$thenetworkId.'",
                "mobile_number": "'.$body->phone.'",
                "Ported_number":'.$ported_number.',
                "request-id" : "'.$body->ref.'",
                "plan": "'.$actualPlanId.'"
            }',
            
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Token $apiKey"
            ),
            ));

            $exereq = curl_exec($curl);
            $err = curl_error($curl);
            
            if($err){
                $response["status"] = "fail";
                $response["msg"] = "Server Connection Error"; //.$err;
                file_put_contents("data_error_log2.txt",json_encode($response)." ".$err." ".$host);
                curl_close($curl);
                return $response;
            }

            $result=json_decode($exereq);
            curl_close($curl);
            

            if($result->Status=='successful' || $result->Status=='processing' || $result->status=='successful' || $result->status=='success' || $result->Status=='success'){
                $response["status"] = "success";
                return $response;
            }
            elseif($result->Status=='failed'){
                $response["status"] = "fail";
                $response["msg"] = "Network Error, Please Try Again Later";
                return $response;
            }
            else{
                $response["status"] = "fail";
                $response["msg"] = "Server/Network Error: ".$result->error[0];
                file_put_contents("data_error_log.txt",json_encode($result));
                return $response;
            }

            return $response;
		}

        //Purchase Data
		public function purchaseDataWithBasicAuthentication($body,$host,$hostuserurl,$apiKey,$thenetworkId,$actualPlanId){

			$response = array();
            

            // ------------------------------------------
            //  Get User Access Token
            // ------------------------------------------
            
            if($body->ported_number == "false"){$ported_number=false;} else{$ported_number=true;}

            $curlA = curl_init();
            curl_setopt_array($curlA, array(
                CURLOPT_URL => $hostuserurl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic  $apiKey",
                    'Content-Type: application/json'
                ),
            ));
        
            $exereqA = curl_exec($curlA);
            $err = curl_error($curlA);
            
            if($err){
                $response["status"] = "fail";
                $response["msg"] = "Server Connection Error"; //.$err;
                curl_close($curlA);
                return $response;
            }
            $resultA=json_decode($exereqA);
            $apiKey=$resultA->AccessToken;
            curl_close($curlA);
        
            
            // ------------------------------------------
            //  Purchase Data
            // ------------------------------------------
        
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "network": "'.$thenetworkId.'",
                "phone": "'.$body->phone.'",
                "bypass":"'.$ported_number.'",
                "request-id" : "'.$body->ref.'",
                "data_plan": "'.$actualPlanId.'"
            }',
            
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Token $apiKey"
            ),
            ));

            $exereq = curl_exec($curl);
            $err = curl_error($curl);
            
            if($err){
                $response["status"] = "fail";
                $response["msg"] = "Server Connection Error"; //.$err;
                file_put_contents("basic_error_log2.txt",json_encode($response));
                curl_close($curl);
                return $response;
            }

            $result=json_decode($exereq);
            curl_close($curl);
            

            if($result->Status=='successful' || $result->Status=='success'){
                $response["status"] = "success";
            }
            elseif($result->status=='fail'){
                $response["status"] = "fail";
                $response["msg"] = "Network Error, Please Try Again Later";
                 file_put_contents("net.txt",json_encode($result));
            }
            else{
                $response["status"] = "fail";
                $response["msg"] = "Server/Network Error: ".$result->error[0];
                file_put_contents("basic_data_error_log.txt",json_encode($result));
            }

            return $response;
		}
		
	public function purchaseDataWithBearerAuthentication($body,$host,$hostuserurl,$apiKey,$thenetworkId,$actualPlanId){

			$response = array();
            
            
            if($body->ported_number == "false"){$ported_number=false;} else{$ported_number=true;}
		  // ------------------------------------------
            //  Purchase Data
            // ------------------------------------------
        
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://smeplug.ng/api/v1/data/purchase',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "network_id": "'.$thenetworkId.'",
                "phone": "'.$body->phone.'",
                "bypass":"'.$ported_number.'",
                "customer_reference" : "'.$body->ref.'",
                "plan_id": "'.$actualPlanId.'"
            }',
            
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer 9ea38043a6fd2eba668c3b97e55161bf704a72bbf87efb5381d736704a1f918c"
            ),
            ));

            $exereq = curl_exec($curl);
            $err = curl_error($curl);
            
            if($err){
                $response["status"] = "fail";
                $response["msg"] = "Server Connection Error"; //.$err;
                file_put_contents("basic_error_log2.txt",json_encode($response));
                curl_close($curl);
                return $response;
            }

            $result=json_decode($exereq);
            curl_close($curl);
            

            if($result->Status=='successful' || $result->Status=='success' || $result->status== true){
                $response["status"] = "success";
            }
            elseif($result->status=='fail'){
                $response["status"] = "fail";
                $response["msg"] = "Network Error, Please Try Again Later";
                 file_put_contents("net.txt",json_encode($result));
            }
            else{
                $response["status"] = "fail";
                $response["msg"] = "Server/Network Error: ".$result->error[0];
                file_put_contents("basic_data_error_log.txt",json_encode($result));
            }

            return $response;
		}
		
	
	public function purchaseDataWithAutopilot($body,$host,$hostuserurl,$apiKey,$thenetworkId,$actualPlanId){

			$response = array();
            
            
            if($body->ported_number == "false"){$ported_number=false;} else{$ported_number=true;}
		  // ------------------------------------------
            //  Purchase Data
            // ------------------------------------------
        
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://smeplug.ng/api/v1/data/purchase',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "networkId": "'.$thenetworkId.'",
                "dataType": "'.$body->type.'",
                "planId": "'.$actualPlanId.'"
                "phone": "'.$body->phone.'",
                "reference" : "'.$body->ref.'",
                "bypass":"'.$ported_number.'",
            }',
            
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer live_788cf93fca054ac18eec374ac4cb96795ntsn139"
            ),
            ));

            $exereq = curl_exec($curl);
            $err = curl_error($curl);
            
            if($err){
                $response["status"] = "fail";
                $response["msg"] = "Server Connection Error"; //.$err;
                file_put_contents("basic_error_log2.txt",json_encode($response));
                curl_close($curl);
                return $response;
            }

            $result=json_decode($exereq);
            curl_close($curl);
            

            if($result->Status=='successful' || $result->Status=='success' || $result->status== true){
                $response["status"] = "success";
            }
            elseif($result->status=='fail'){
                $response["status"] = "fail";
                $response["msg"] = "Network Error, Please Try Again Later";
                 file_put_contents("net.txt",json_encode($result));
            }
            else{
                $response["status"] = "fail";
                $response["msg"] = "Server/Network Error: ".$result->error[0];
                file_put_contents("basic_data_error_log.txt",json_encode($result));
            }

            return $response;
		}
        

    }

?>