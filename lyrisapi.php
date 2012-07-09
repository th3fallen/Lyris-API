<?php

    /**
     * LyrisHQ API Library
     * @author Clark Tomlinson
     * @package LyrisAPI
     * @since Jun 19, 2012, 12:16:32 PM
     * @link http://www.clarkt.com
     * @copyright 2012
     */

    namespace Lyris;

    /**
     * Handles all LyrisHQ API interactions
     */
    class API {

        /**
         * Stores siteID for class functions
         * @var
         */
        private $siteid;

        /**
         * Stores global api password for all requests
         * @var null
         */
        private $apipassword;


        /**
         * Gathers Site id and global api password for requests
         * @param      $siteid
         * @param null $apipassword
         */
        public function __construct($siteid, $apipassword = NULL) {
            //set site id
            $this->siteid = $siteid;

            //set global api password
            if (!is_null($apipassword)) {
                $this->apipassword = $apipassword;
            }
        }

        /**
         * LIST FUNCTIONS
         * @see Lyris API Docs Block 2.0
         *  Omitted 2.5, 2.6
         */

        /**
         * Add Mailing List
         * @see Block 2.1
         * @param string $name
         * @param array  $attributes
         * @param string $listapipass
         * @throws \Exception
         * @return array
         */
        public function listAdd($name, array $attributes = array(), $listapipass = NULL) {

            //if api pass is set in constructor assume lyris global api pass is enabled and use for all requests
            $this->setApiPassword($listapipass);

            if (empty($name)) {
                die('List name required');
            }

            //loop through attributes and add to query
            $attributesstring = '';
            if (!empty($attributes)) {
                foreach ($attributes as $k => $v) {
                    $attributesstring .= PHP_EOL . '<DATA type="extra" id="' . strtoupper(trim($k)) . '">' . trim($v) . '</DATA>' . PHP_EOL;
                }
            }

            //build data for query
            $querydata = array(
                'type'     => 'list', 'activity' => 'add', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="name">' . $name . '</DATA>' . $attributesstring . '</DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Adding of list failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            foreach ($responseobj->DATA as $value) {
                $returnarray[(string) $value['type']] = (string) $value;
            }

            return $returnarray;
        }

        /**
         * Delete Mailing List
         * @see Block 2.2
         * @param string $mlid
         * @param        $listapipass
         * @throws \Exception
         * @return array
         */
        public function listDelete($mlid, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build query array
            $querydata = array(
                'type'     => 'list', 'activity' => 'delete', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <MLID>' . $mlid . '</MLID>
             </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Removing of list failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['message'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * Query for all lists and relevent data
         * @see Block 2.3
         * @param string $listapipass
         * @throws \Exception
         * @return array
         */
        public function listQuery($listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build query array
            $querydata = array(
                'type'     => 'list', 'activity' => 'query-listdata', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //create new SimpleXMLElement Instance
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('List Query failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $i = 0;
            foreach ($responseobj->RECORD as $value) {

                $returnarray[$i] = array();

                //add list id as array value
                $returnarray[$i]['mlid'] = (string) $value->DATA['id'];

                foreach ($value->DATA as $k => $v) {

                    $returnarray[$i][(string) $v['type']] = (string) $v;

                }
                $i++;
            }

            return $returnarray;
        }

        /**
         * Edit Mailing List
         * @see Block 2.4
         * @param string $mlid
         * @param string $name
         * @param string $from_name
         * @param string $from_email
         * @param string $listapipass
         * @throws \Exception
         * @return array
         */
        public function listEdit($mlid, $name, $from_name, $from_email, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build data for query
            $querydata = array(
                'type'     => 'list', 'activity' => 'edit', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="name">' . $name . '</DATA>
            <DATA type="extra" id="FROM_NAME">' . $from_name . '</DATA>
            <DATA type="extra" id="FROM_EMAIL">' . $from_email . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Editing of list failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['message'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * MEMBER FUNCTIONS
         * @see Lyris API Docs Block 4.0
         */

        /**
         * Add member to list
         * @see Block 4.1
         * @param int     $mlid
         * @param string  $email
         * @param array   $attributes
         * @param array   $demographics
         * @param string  $listapipass
         * @throws \Exception
         * @return array
         */
        public function memberAdd($mlid, $email, array $attributes, array $demographics = NULL, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //loop through attributes and add to query
            $attributesstring = '';
            if (!empty($attributes)) {
                foreach ($attributes as $k => $v) {
                    $attributesstring .= PHP_EOL . '<DATA type="extra" id="' . strtoupper(trim($k)) . '">' . trim($v) . '</DATA>' . PHP_EOL;
                }
            }

            //loop through demographics and add to query
            $demographicsstring = '';
            if (!empty($demographics)) {
                foreach ($attributes as $k => $v) {
                    $demographicsstring .= PHP_EOL . '<DATA type="demographic" id="' . strtoupper(trim($k)) . '">' . trim($v) . '</DATA>' . PHP_EOL;
                }
            }

            //build data for query
            $querydata = array(
                'type'     => 'record', 'activity' => 'add', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="email">' . $email . '</DATA>' . $attributesstring . $demographicsstring . '</DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);
            //check for success
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Adding Member failed with message: ' . (string) $responseobj->DATA);
            }
            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['memberid'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * Download Members of Mailing list
         * @see Block 4.2
         * @param type   $mlid
         * @param type   $email
         * @param type   $type
         * @param string $listapipass
         * @throws \Exception
         * @return array
         */
        public function memberDownload($mlid, $email, $type, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build data for query
            $querydata = array(
                'type'     => 'record', 'activity' => 'download', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="extra" id="email_notify">' . $email . '</DATA>
            <DATA type="extra" id="type">' . $type . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Member download failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;

            return $returnarray;
        }

        /**
         * Query Members
         * @see Block 4.3
         * @param numeric $mlid
         * @param string  $email
         * @param string  $listapipass
         * @throws \Exception
         * @return array
         */
        public function memberQuery($mlid, $email, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build data for query
            $querydata = array(
                'type'     => 'record', 'activity' => 'query-data', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="email">' . $email . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Member Query failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;

            if ($returnarray['status'] == 'error') {
                $returnarray['message'] = (string) $responseobj->DATA;
            }

            foreach ($responseobj->RECORD->DATA as $value) {
                $returnarray[(string) $value['id']] = (string) $value;
            }

            return $returnarray;
        }


        /**
         * Will query all memeber status
         */
        public function memberQueryStats() {
            //TODO: Query Stats
        }

        /**
         * Query list for member data
         * @see Block 4.5
         * @param      $mlid
         * @param      $type
         * @param null $listapipass
         * @throws \Exception
         * @return array
         */
        public function memberQueryList($mlid, $type, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build data for query
            $querydata = array(
                'type'     => 'record', 'activity' => 'query-listdata', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="extra" id="type">' . $type . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Query member list failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;

            if ($returnarray['status'] == 'error') {
                $returnarray['message'] = (string) $responseobj->DATA;
            }

            foreach ($responseobj->RECORD->DATA as $value) {
                if ((string) $value['type'] == 'email') {
                    $returnarray[(string) $value['type']] = (string) $value;
                } else {
                    $returnarray[(string) $value['id']] = (string) $value;
                }
            }

            return $returnarray;

        }

        /**
         * Edit List Member(Subscriber)
         * @see Block 4.6
         * @param string $mlid
         * @param string $email
         * @param array  $attributes
         * @param array  $demographics
         * @param        $listapipass
         * @throws \Exception
         * @return array
         */
        public function memberEdit($mlid, $email, array $attributes, array $demographics = NULL, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //loop through attributes and add to query
            $attributesstring = '';
            if (!empty($attributes)) {
                foreach ($attributes as $k => $v) {
                    $attributesstring .= PHP_EOL . '<DATA type="extra" id="' . trim($k) . '">' . trim($v) . '</DATA>' . PHP_EOL;
                }
            }

            //loop through demographics and add to query
            $demographicsstring = '';
            if (!empty($demographics)) {
                foreach ($attributes as $k => $v) {
                    $demographicsstring .= PHP_EOL . '<DATA type="demographic" id="' . trim($k) . '">' . trim($v) . '</DATA>' . PHP_EOL;
                }
            }

            //build data for query
            $querydata = array(
                'type'     => 'record', 'activity' => 'update', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="email">' . $email . '</DATA>' . $attributesstring . $demographicsstring . '</DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Editing of member failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['memberid'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * Message Management
         * @see Lyris API Docs Block 5.0
         */

        /**
         * Add Message
         * @see Block 5.1
         * @param string   $mlid
         * @param string   $fromEmail
         * @param string   $fromName
         * @param string   $subject
         * @param string   $messageFormat
         * @param string   $messageText
         * @param string   $messageHTML
         * @param          $listapipass
         * @throws \Exception
         * @return array
         */
        public function messageAdd($mlid, $fromEmail, $fromName, $subject, $messageFormat, $messageText, $messageHTML, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //check if format is html if so add messagehtml to querydata
            if (strtoupper($messageFormat) == 'HTML') {
                $html = '<DATA type="message-html">' . htmlentities($messageHTML) . '</DATA>';
            } else {
                $html = '';
            }

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'add', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="subject">' . $subject . '</DATA>
            <DATA type="from-email">' . $fromEmail . '</DATA>
            <DATA type="from-name">' . $fromName . '</DATA>
            <DATA type="message-format">' . $messageFormat . '</DATA>
            <DATA type="message-text">' . $messageText . '</DATA>' . $html . '</DATASET>
            <DATA type="charset">UTF-8</DATA>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check if query was successful if not throw exception
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Adding Message Failed with message: ' . $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['messageid'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * Copy message
         * @see Block 5.2
         * @param      $mlid
         * @param      $mid
         * @param null $listapipass
         * @throws \Exception
         * @return array
         */
        public function messageCopy($mlid, $mid, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'copy', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MID>' . $mid . '</MID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Coping of message failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['messageid'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * Proof a created message
         * @see Block 5.3
         * @param      $mlid
         * @param      $mid
         * @param      $text
         * @param null $listapipass
         * @throws \Exception
         * @return array
         */
        public function messageProof($mlid, $mid, $text, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'proof', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MID>' . $mid . '</MID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="text">' . $text . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Message proofing failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['message'] = (string) $responseobj->DATA;
            foreach ($responseobj->EMAIL as $email) {
                $returnarray['email'][] = (string) $email;
            }

            return $returnarray;
        }

        /**
         * Gathers all stats of single message
         * @param      $mlid
         * @param      $mid
         * @param null $listapipass
         * @return array
         * @throws \Exception
         */
        public function messageQueryData($mlid, $mid, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'query-data', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <MID>' . $mid . '</MID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Message data query failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;

            foreach ($responseobj->RECORD->DATA as $value) {
                $returnarray[(string) $value['type']] = (string) $value;
            }

            return $returnarray;
        }

        /**
         * Get all messages in a list
         * @see Block 5.6
         * @param      $mlid
         * @param null $startdate
         * @param null $enddate
         * @param null $listapipass
         * @throws \Exception
         * @return array
         */
        public function messageQueryListData($mlid, $startdate = NULL, $enddate = NULL, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'query-listdata', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Message list query failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['messageid'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * Query Messages Stats
         * @see Block 5.6
         * @param      $mid
         * @param      $mlid
         * @param      $action
         * @param null $params
         * @param null $listapipass
         * @throws \Exception
         * @return XML
         */
        public function messageQueryStats($mid, $mlid, $action, $params = NULL, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            if ($action == 'clicked-details-background') {
                $email = '<DATA type="extra" id="email">' . $params['email'] . '</DATA>';
            } else {
                $email = '';
            }

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'query-stats', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MID>' . $mid . '</MID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="' . strtoupper($action) . '"></DATA>' . $email . '</DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Query message stats failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['messageid'] = (string) $responseobj->DATA;

            echo $querydata;
            return $response;
        }

        /**
         * Schedule Message
         * @see Block 5.8
         * @param       $mlid
         * @param       $mid
         * @param       $action
         * @param       $timestamp
         * @param array $attributes
         * @param null  $listapipass
         * @throws \Exception
         * @return array
         */
        public function messageSchedule($mlid, $mid, $action, $timestamp = NULL, array $attributes = NULL, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //check if action is schedule if so add delivery dates to query
            $timestampstring = '';
            //if action is schedule and timestamp is after current date add them to the query if not leave null for immediate send
            if ($action == 'schedule' && strtotime($timestamp) > time()) {
                $timestampstring .= '<DATA type="delivery-year">' . date('Y', strtotime($timestamp)) . '</DATA>';
                $timestampstring .= '<DATA type="delivery-month">' . date('n', strtotime($timestamp)) . '</DATA>';
                $timestampstring .= '<DATA type="delivery-day">' . date('j', strtotime($timestamp)) . '</DATA>';
                $timestampstring .= '<DATA type="delivery-hour">' . date('G', strtotime($timestamp)) . '</DATA>';
            }

            //loop through attributes and add to query
            $attributesstring = '';
            if (!empty($attributes)) {
                foreach ($attributes as $k => $v) {
                    $attributesstring .= PHP_EOL . '<DATA type="' . strtoupper(trim($k)) . '">' . trim($v) . '</DATA>' . PHP_EOL;
                }
            }

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'schedule', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MID>' . $mid . '</MID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="action">' . $action . '</DATA>' . $timestampstring . $attributesstring . '</DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check if response was successful if not throw exception
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Schedualing of message failed with message: ' . $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['messageid'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * Edit An already created message
         * @see Block 5.9
         * @param      $mlid
         * @param      $mid
         * @param      $fromEmail
         * @param      $fromName
         * @param      $subject
         * @param      $messageFormat
         * @param      $messageText
         * @param      $messageHTML
         * @param null $listapipass
         * @throws \Exception
         * @return array
         */
        public function messageEdit($mlid, $mid, $fromEmail, $fromName, $subject, $messageFormat, $messageText, $messageHTML, $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //check if format is html if so add messagehtml to querydata
            if (strtoupper($messageFormat) == 'HTML') {
                $html = '<DATA type="message-html">' . htmlentities($messageHTML) . '</DATA>';
            } else {
                $html = '';
            }

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'update', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MID>' . $mid . '</MID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="subject">' . $subject . '</DATA>
            <DATA type="from-email">' . $fromEmail . '</DATA>
            <DATA type="from-name">' . $fromName . '</DATA>
            <DATA type="message-format">' . $messageFormat . '</DATA>
            <DATA type="message-text">' . $messageText . '</DATA>' . $html . '</DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Editing of message failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['messageid'] = (string) $responseobj->DATA;

            return $returnarray;
        }

        /**
         * Send quick proof of message to emails
         * @see Block 5.10
         * @param        $mlid
         * @param        $mid
         * @param        $emails
         * @param string $contentanalyzer
         * @param string $inboxsnap
         * @param string $blmon
         * @param string $multi
         * @param null   $listapipass
         * @throws \Exception
         * @return array
         */
        public function messageQuickTest($mlid, $mid, $emails, $contentanalyzer = 'off', $inboxsnap = 'off', $blmon = 'off', $multi = '1', $listapipass = NULL) {

            //check api password
            $this->setApiPassword($listapipass);

            //explode emails array into csv format
            if (is_array($emails)) {
                $emails = implode(', ', $emails);
            }

            //build data for query
            $querydata = array(
                'type'     => 'message', 'activity' => 'message-quicktest', 'input'    => '<DATASET>
            <SITE_ID>' . $this->siteid . '</SITE_ID>
            <MID>' . $mid . '</MID>
            <MLID>' . $mlid . '</MLID>
            <DATA type="extra" id="password">' . $this->apipassword . '</DATA>
            <DATA type="extra" id="emails">' . $emails . '</DATA>
            <DATA type="extra" id="content_analyzer">' . $contentanalyzer . '</DATA>
            <DATA type="extra" id="inbox_snapshot">' . $inboxsnap . '</DATA>
            <DATA type="extra" id="back_list_monitor">' . $blmon . '</DATA>
            <DATA type="extra" id="multi">' . $multi . '</DATA>
            </DATASET>'
            );

            //submit data
            $response = $this->submit($querydata);
            //convert xml to array
            $responseobj = new \SimpleXMLElement($response);

            //check for errors
            if ((string) $responseobj->TYPE !== 'success') {
                throw new \Exception('Message quick test failed with message: ' . (string) $responseobj->DATA);
            }

            //create blank array to bind items to
            $returnarray = array();
            //clean up result and return array
            $returnarray['status'] = (string) $responseobj->TYPE;
            $returnarray['message'] = (string) $responseobj->DATA;
            $returnarray['htmlmid'] = (string) $responseobj->HTML_MID;
            $returnarray['textmid'] = (string) $responseobj->TEXT_MLID;
            $returnarray['contentanalyizer'] = (string) $responseobj->CONTENT_ANALYZER;
            $returnarray['inboxsnap'] = (string) $responseobj->INBOX_SNAPSHOT;

            return $returnarray;
        }

        /**
         * Sets provided api password for use
         * @param $password
         */
        protected final function setApiPassword($password) {
            if (empty($this->apipassword)) {
                $this->apipassword = $password;
            }
        }

        /**
         * POSTS REQUESTS TO LYRIS API
         * @param array $data
         * @return XML
         */
        private final function submit(array $data) {
            // set url var
            $url = 'https://www.elabs10.com/API/mailing_list.html';
            // open connection
            $ch = curl_init();
            // set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            // execute post
            $result = curl_exec($ch);
            // close connection
            curl_close($ch);

            return $result;
        }

    }