<?php
  date_default_timezone_set('UTC');
  set_time_limit(0);
  $max = 100000;
  $sub = "minecraft";
  $getReplies = "false";
  $count = 0;
  $after = "fake";
  $end = time();
  $day = 86400;
  $stopFile = "stop.txt";
  $runFile = "running.txt";
   
  if (file_exists($runFile)) {
     $myfile = fopen($runFile, "r") or die("Unable to open file!");
     $workingSub = fread($myfile,filesize($runFile));
     fclose($myfile);
     echo "Work is in progress on subreddit " . $workingSub . " check <a href='/?sub=" . $workingSub . "'>here</a> periodically to see if it has completed.";
     exit();
  }

  $options = getopt("s:m:r:");
  if (isset($options["m"])) {
     $max = $options["m"];
  }

  if (isset($options["s"])) {
     $sub = $options["s"];
  }

  if (isset($options["r"])) { 
     $getReplies = $options["r"];
  }

  $zipFile = $sub . "/" . $sub . ".zip";
  if (file_exists($sub . "/done.txt")) {
    echo "File exists, download it <a href='" . $zipFile . "'>here</a>";
    exit();
  }
 
  if (file_exists($sub)) {
    echo "Work is in progress on subreddit " . $sub . ", check back here for the zip file when it has completed.";
    exit();
  }
  
  unlink("cat.out");
  echo "Work started on subreddit " . $sub . ", check back here for the zip file when it has completed.";

  $baseUrl =  "http://www.reddit.com/r/" . $sub . "/search.json?sort=top&restrict_sr=on&syntax=cloudsearch&limit=100&q=timestamp%3A";
  $replyBase = "http://www.reddit.com";
  mkdir($sub);

  $myfile = fopen($runFile, "w");
  $txt = $sub;
  fwrite($myfile, $txt);
  fclose($myfile);
  
  $myfile = fopen($sub . "/" . $sub . ".csv", "w");
  $header = array("Reddit ID", "Author", "Title", "Content", "Upvotes", "Reply To");
  fputcsv($myfile, $header);
  fclose($myfile);
  while ($count < $max && !empty($after)) {
    $after = "";
    if (file_exists($stopFile)) {
      exec("zip -r " . $zipFile . " "  . $sub . "/");
      $myfile = fopen($sub . "/done.txt", "w") or die("Unable to open file!");
      $txt = "Done";
      fwrite($myfile, $txt);
      fclose($myfile);

      unlink($runFile);	
      unlink($stopFile);	
      exit();
    }
    $start = $end - $day;
    sleep(5);
    $json = file_get_contents($baseUrl . $start . ".." . $end);
    $retry = 0;
    while(empty($json) && $retry < 10) {
	sleep(2);	
	error_log("Retrying # " . $retry . ": " . $baseUrl . $start . ".." . $end);
    	$json = file_get_contents($baseUrl . $start . ".." . $end);
        $retry++;
    }	
	
    $obj = json_decode($json);

    $contents = $obj->data->children;
    foreach($contents as $content) {
      $data = $content->data;
      $num = $data->num_comments;
      if ($num >= 10) {
        $postContent = $data->selftext;
        if (empty($postContent)) {
            $postContent = $data->url;
        }
        $fields = array($data->name, $data->author, $data->title, $postContent, $data->score, 'nothing');
        $myfile = fopen($sub . "/" . $sub . ".csv", "a");
        fputcsv($myfile, $fields);
        fclose($myfile);
    
   	echo "Wrote post for: " . $data->title . "\n";
   	error_log(date("F j, Y, g:i a") . ": Wrote post for: " . $data->title . "\n",3, "cat.out");
        
 	if ($getReplies != "false") {
          sleep(2);
          $replyJson = file_get_contents($replyBase . $data->permalink . ".json");
	  $retry = 0;
          while (empty($replyJson) && $retry < 10) {	
            sleep(2);
            $replyJson = file_get_contents($replyBase . $data->permalink . ".json");
            $retry++;
          }

          $robj = json_decode($replyJson);
          $replyContents = $robj[1]->data->children;
          foreach($replyContents as $replyContent) {
            $replyData = $replyContent->data;
            parseReplyTree($replyData, $sub);
          }
	}
        $count++;
      }
      $after = $data->name;
    } 
    $end = $start - 1;
  } 

  exec("zip -r " . $zipFile . " "  . $sub . "/");
  $myfile = fopen($sub . "/done.txt", "w") or die("Unable to open file!");
  $txt = "Done";
  fwrite($myfile, $txt);
  fclose($myfile);
  
  unlink($runFile);
  $message = "The run for " . $sub . " has completed.  You can get your dumb file here: 198.101.158.143/" . $zipFile . "\n\nClean up after you've downloaded, why don't you?  198.101.158.143/?sub=" . $sub . "&delete=true";
  mail('gcastanon@gmail.com', 'Reddit Runner Ran', $message);


function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
} 

function parseReplyTree($replyData, $sub) {
    if (isset($replyData->body)) {
        $replyFields = array($replyData->name, $replyData->author, "nothing", $replyData->body, $replyData->score, $replyData->parent_id);
        
        $myfile = fopen($sub . "/" . $sub . ".csv", "a");
        fputcsv($myfile, $replyFields);
        fclose($myfile);
        
        if ($replyData->replies != "") {
            foreach ($replyData->replies->data->children as $reply) {
                parseReplyTree($reply->data, $sub);
            }
        }
    }
}
?>
