<?php
include 'connect.php';

class Game
{   
    private $id;
    private $playerId;
    private $categoryId;
    private $questions; // messed up, this is not what it looks like {question: {id: text}, options: {id: text, id: text, id: text, id:text}}}}
    private $dbh;
    
    function __construct($playerId, $categoryId, $num)
    {    
        $this->id = time();
        $this->playerId = $playerId;
        $this->categoryId = $categoryId;
        
        // TODO: a general database CLass is needed
        global $hostname,$username, $password, $dbname;
        $this->dbh = new mysqli($hostname, $username,$password,$dbname);
        
        $this->fetchQuestions($num);
        $this->fetchOptions($num);
        //$this->writeToGamePlayer();
       // $this->writeToPlayerList();
       // $this->writeQuestionsToGameQuestion();
    }
    
    public static function score($submittedAnswers)
    {
        global $hostname,$username, $password, $dbname;
        $dbh = new mysqli($hostname, $username,$password,$dbname);
        $answers = json_decode($submittedAnswers, true);
        $total = count($answers);
        $correct = 0;
		$wrong = 0;

        // Check whether player’s answer is correct from db
        

        foreach ($answers as $question_id => $answer_id) {
           $stmt = $dbh->query("SELECT correct
                                FROM answers
                                WHERE id=$answer_id;");
            //$result = $stmt->fetch(PDO::FETCH_ASSOC);
            $result = $stmt->fetch_array(MYSQLI_ASSOC);
			//echo $result['correct'];
            if ($result['correct'] == 1) $correct++;
			else if ($result['correct'] == 0) $wrong++;
        }
        
       

        $ret['correct'] = $correct;
        $ret['wrong'] = $wrong;
        
        return json_encode($ret);
    }

    public static function endGame($gameId, $playerId)
    {
        global $hostname,$username, $password, $dbname;
        $dbh = new mysqli($hostname, $username,$password,$dbname);

        // remove user from game_player table
        //$stmt = $dbh->prepare("DELETE FROM game_player WHERE game_id=$gameId;");
       // $stmt->execute();

        // change user's status to '0' from player_list table
		date_default_timezone_set('America/Los_Angeles');
		$time=date("YmdHis");
        $stmt = $dbh->prepare("UPDATE player_list 
                                SET status=0,time=$time
                                WHERE username='$playerId';");
        $stmt->execute();

        $dbh = null;
        
        unset($_SESSION['gameId']);
        unset($_SESSION['playerId']);
        unset($_SESSION['questions']);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getQuestions()
    {
        return $this->questions;
    }
    private function num_of_rows()
	{
	$stmt = $this->dbh->query("SELECT *
                                    FROM questions
                                    WHERE category_id=$this->categoryId;");
    
	$result= $stmt->num_rows;
	return $result;
	}
	
    private function fetchQuestions($num)
    {
        $rows=$this->num_of_rows()-1;
      //  echo $rows;
		$i=0;
        while($i < $num) // fill the content of into $questions
        {   
		    $row=mt_rand(0,$rows);
			
		    $stmt = $this->dbh->query("SELECT id, text
                                    FROM questions
                                    WHERE category_id=$this->categoryId limit $row,1 ;");
            $result = $stmt->fetch_array(MYSQLI_ASSOC);
            
            $questionId = $result['id'];
			
		    $text = $result['text'];
			if($this->check($questionId)==0)
			{
			$this->questions[$i]['question']['id'] = $questionId;
            $this->questions[$i]['question']['text'] = $text;
		    $i++;
			
			}
        }
		
    }
    private function check($id)
	{
	 if (is_array($this->questions)){
	 foreach($this->questions as $question)
	 {
	 //echo $question['question']['id']." ";
	 if ($question['question']['id']==$id) {
	 return 1;
	 break;}
	 	 }
	 return 0;
	 }
	 else return 0;
	}
    private function fetchOptions($num)
    {
       for ($i = 0; $i < $num; $i++) {
            $questionId = $this->questions[$i]['question']['id'];
			$stmt = $this->dbh->query('SELECT id, text
                                    FROM answers
                                    WHERE question_id='.$questionId);
			//echo $stmt;			
            $option_num = 0;
          while ($option = $stmt->fetch_array(MYSQLI_ASSOC)) 
		  {     
                $id = $option['id'];
				$text = $option['text'];
                $this->questions[$i]['options']['option' . $option_num]['id'] = $id;
                $this->questions[$i]['options']['option' . $option_num]['text'] = $text;
                $option_num++;
				
            }
        }
    }
    
    public static function writeToGamePlayer($gameid,$player,$correct,$usedtime,$category)
    {   global $hostname,$username, $password, $dbname;
        $dbh = new mysqli($hostname, $username,$password,$dbname);       
        $stmt = $dbh->prepare("INSERT INTO game_player
                                    VALUES ('$gameid', '$player', $correct, '$usedtime', $category);");
        $stmt->execute();
    }
    
  
    
    public static function writeQuestionsToGameQuestion($player,$gameid,$questions)
    {   global $hostname,$username, $password, $dbname;
        $dbh = new mysqli($hostname, $username,$password,$dbname);
		
		foreach($questions as $question){
		$qid= $question['question']['id'];
		$qtext=$question['question']['text'];
		$stmt = $dbh->prepare("INSERT INTO game_question
                                    VALUES ('$gameid','$qid','$qtext');");
		$stmt->execute();
		}

    }
}
?>
