<?php 
	require_once 'constant.php';
	require_once '../db.class.php';

	session_start();

	if (! isset($_SESSION['current_user'])) {
		header("Location: " . ADMIN_BASE_PATH . "/login.php");
	} else if (isset($_POST['question']) && isset($_POST['question_code']) && isset($_POST['option']) && ! empty($_POST['question']) && ! empty($_POST['question_code']) && ! empty($_POST['option'])) {
		$question = substr($_POST['question'], 0, 140);

		if (substr($_POST['question_code'], 0, 1) != "#") {
			$_SESSION["error"] = "No hashtag in the question_code for e.g. #question_code";
			header("Location: " . ADMIN_BASE_PATH . "/questions.php");
		}

		$question_code = $_POST['question_code'];

		if (isset($_POST['update']) && isset($_POST['question_id']) && ! empty($_POST['question_id'])) {
			//update data
			
			$stm = "questions = '" . $question . "'";
			$stm .= ", question_code = '" . $question_code . "'";

			foreach ($_POST['option'] as $key => $value) {
				if (! empty($value)) {
					$stm .= ", option_" . $key . " = '" . $value . "'";
				}
			}

			$stm = "UPDATE questions SET " . $stm . " WHERE id = " . $_POST['question_id'];

		} else {
			// Insert data
			
			$field = "(questions, question_code";
			$option_value = "('" . $question . "', '" . $question_code . "'";
			
			end($_POST['option']);
			$last_index = key($_POST['option']);
			
			foreach ($_POST['option'] as $key => $value) {
				if (! empty($value)) {
					$field .= ", option_" . $key;
					$option_value .= ", '" . $value . "'";
				}

				if ($last_index == $key) {
					$field .= ")";
					$option_value .= ")";
				} 
				
			}
					
			$stm = "INSERT INTO questions " . $field . " VALUES " . $option_value;
		}

		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();
		$result = $connection->createQuery($stm);
		$connection->closeConnection();
		if ($result) {
			header("Location: " . ADMIN_BASE_PATH . "/questions.php");
		}
	} else if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['question']) && ! empty($_GET['question'])) {
		// Delete data
		$stm = "DELETE FROM questions WHERE id = " . $_GET['question'];
		$connection = new DBConnection();
		$connection->db_connection();
		$connection->selectDb();
		$result = $connection->deleteQuery($stm);
		$connection->closeConnection();
		header("Location: " . ADMIN_BASE_PATH . "/questions.php");
		exit(); 
	} else {
		//Edit and add data
		if (isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['question']) && ! empty($_GET['question'])) { 
			$stm = "SELECT * from questions WHERE id = " . $_GET['question'];
			$connection = new DBConnection();
			$connection->db_connection();
			$connection->selectDb();
			$result = $connection->selectQuery($stm);
			$connection->closeConnection();
			$open_hidden = true;
		}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Delphi technique using social media</title>
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap-responsive.css">
		<script src="../js/jquery-2.0.3.min.js"></script>
		<script src="../bootstrap/js/bootstrap.js"></script>
		<script src="../bootstrap/js/application.js"></script>
	</head>
	<body>

		<div class="container">
			<div class="row-fluid">
				<div class="span12">
					<a href="<?php echo ADMIN_BASE_PATH ?>/index.php">Back to navigation</a>	
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12">
					<ul style="text-align:center;list-style-type:none;">
						<li><span class="label label-info">NOTE!!! Respective hashtag should be used to reply the answer in each round</span></li>
						<li><span class="label label-info">Round 1 => #R1_answer</span></li>
						<li><span class="label label-info">Round 2 => #R2_answer </span></li>
						<li><span class="label label-info">Round 3 => #R3_answer, #R3_comment</span></li>
						
							 <?php 
								if (! empty($_SESSION['no_feedback'])) {
									echo '<li><span class="label label-important">';
									echo "<p>" . $_SESSION['no_feedback'] ."</p>";
									echo "</span></li>";
									unset($_SESSION['no_feedback']);
								}

								if (! empty($_SESSION['yes_feedback'])) {
									echo '<li><span class="label label-success">';
									echo "<p>" . $_SESSION['yes_feedback'] ."</p>";
									echo "</span></li>";
									unset($_SESSION['yes_feedback']);
								}

								if (! empty($_SESSION['tweet_fail'])) {
									echo '<li><span class="label label-success">';
									echo "<p>" . $_SESSION['tweet_fail'] ."</p>";
									echo "</span></li>";
									unset($_SESSION['tweet_fail']);
								}

								if (! empty($_SESSION['tweet_reset'])) {
									echo '<li><span class="label label-success">';
									echo "<p>" . $_SESSION['tweet_reset'] ."</p>";
									echo "</span></li>";
									unset($_SESSION['tweet_reset']);
								}
							?> 
					</ul>
				</div>
			</div>

			<div class="row-fluid">
			  <div class="span3">
					<!-- <form class="form-horizontal" method="POST" action="questions.php"> -->
					<form method="POST" action="questions.php">
						<legend><a href="questions.php">Add questions</a></legend>
						<div class="control-group">
						    <label class="control-label" for="question">Question</label>
							<div class="controls">
						     	<textarea id="question" rows="4" cols="50" name="question" placeholder="Write questions e.g. What are the important actors that hinder to study"><?php echo $result['questions']; ?></textarea>
						    	<p>Total characters left : <span id="text_length">140</span>
						    </div>

						</div>

						<div class="control-group">
						    <label class="control-label" for="question_code">Question code</label>
						    <div class="controls">
						      	<input type="text" id="question_code" name="question_code" placeholder="Question code e.g #Q1_code" value="<?php echo $result['question_code']; ?>">
						    </div>
						    <?php 
								if (! empty($_SESSION['error'])) {
									echo "<p>" . $_SESSION['error'] ."</p>";
									unset($_SESSION['error']);
								}
							?>
						</div>

						<div class="control-group">
						    <label class="control-label" for="option_1">Answer option 1</label>
						    <div class="controls">
						      	<input type="text" id="option_1" name="option[1]" placeholder="E.g. Option1" value="<?php echo $result['option_1']; ?>">
						    </div>
						</div>

						<div class="control-group">
						    <label class="control-label" for="option_2">Answer option 2</label>
						    <div class="controls">
						      	<input type="text" id="option_2" name="option[2]" placeholder="E.g. Option2" value="<?php echo $result['option_2']; ?>">
						    </div>
						</div>
							
						<div class="control-group">
						    <label class="control-label" for="option_3">Answer option 3</label>
						    <div class="controls">
						      	<input type="text" id="option_3" name="option[3]" placeholder="E.g. Option3" value="<?php echo $result['option_3']; ?>">
						    </div>
						</div>	
						
						<div class="control-group">
						    <label class="control-label" for="option_4">Answer option 4</label>
						    <div class="controls">
						      	<input type="text" id="option_4" name="option[4]" placeholder="E.g. Option4" value="<?php echo $result['option_4']; ?>">
						    </div>
						</div>
						
						<div class="control-group">
						    <label class="control-label" for="option_5">Answer option 5</label>
						    <div class="controls">
						      	<input type="text" id="option_5" name="option[5]" placeholder="E.g. Option5" value="<?php echo $result['option_5']; ?>">
						    </div>
						</div>

						<div class="control-group">
						    <label class="control-label" for="option_6">Answer option 6</label>
						    <div class="controls">
						      	<input type="text" id="option_6" name="option[6]" placeholder="E.g. Option6" value="<?php echo $result['option_6']; ?>">
						    </div>
						</div>

						<div class="control-group">
						    <label class="control-label" for="option_7">Answer option 7</label>
						    <div class="controls">
						      	<input type="text" id="option_7" name="option[7]" placeholder="E.g. Option7" value="<?php echo $result['option_7']; ?>">
						    </div>
						</div>
						
						<div class="control-group">
						    <div class="controls">
						    	<?php 
						    		if ($open_hidden) {
						    			echo '<input type="hidden" name="update" value="update">';
						    			echo '<input type="hidden" name="question_id" value="'. $_GET["question"] . '">';
						    			echo '<input class="btn btn-large btn-primary" type="submit" value="Update Question">';
						    		} else {
						    			echo '<input class="btn btn-large btn-primary" type="submit" value="Create Question">';
						    		}
						    	?>
						      	
						    </div>
						</div>
						
					</form>
			  </div>
			  <div class="span9">
					<legend>List of questions <span style="float:right;"><a class="btn btn-small btn-primary" href="tweet_reset.php?action=tweet_reset">Reset tweet status</a> </span></legend>

					<div class="control-group">
					   	<table class="table table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Questions</th>
									<th>Answer options</th>
									<th>Question code</th>
									<th>Tweet status</th>
									<th>Settings</th>
								</tr>
							</thead>
							<tbody>
							<?php 

								$stm = "SELECT * FROM questions";
								$connection = new DBConnection();
								$connection->db_connection();
								$connection->selectDb();
								$result = $connection->selectQuery($stm);
								$connection->closeConnection();
								
								if (! $result) {
									echo "No results";
								} else {
									foreach ($result as $key => $row) {
										$row = array_filter($row);
							?> 
								<tr>
									<td><?php echo $key + 1; ?></td>
									<td style="width:130px;"><?php echo $row['questions']; ?></td>
									<td><?php
										$option = "";
										for ($i = 1; $i <= 7; $i++) {
										    if (! empty($row["option_" . $i])) {
										    	$option .= $i . ") " . $row["option_" . $i] . " <br>";
										    }
										}

										echo $option;
									?></td>
									<td><?php echo $row['question_code']; ?></td>
									<td>
										<ul style="list-style-type:none;margin:0 auto;">
											<li>Question : <?php echo empty($row['question_tweet_count'])? '0' : $row['question_tweet_count']; ?> </li>
											<li>Answer: <?php echo empty($row['answer_tweet_count'])? '0' : $row['answer_tweet_count'];  ?></li>
											<li>R1 feedback: <?php echo empty($row['R1_feedback_tweet_count'])? '0' : $row['R1_feedback_tweet_count']; ?></li>
											<li>R1 feedback: <?php echo empty($row['R2_feedback_tweet_count'])? '0' : $row['R2_feedback_tweet_count']; ?></li>
										</ul>
									</td>
									<td>
										<a href="questions.php?action=edit&question=<?php echo $row['id'];?>">Edit</a>
										<a href="questions.php?action=delete&question=<?php echo $row['id'];?>">Delete</a>
									</td>
								</tr>
								<tr>
									<td></td>
									<td><a class="btn btn-small btn-primary" href="tweet.php?action=tweet_q&question=<?php echo $row['id'];?>">Tweet Question</a></td>
									<td><a class="btn btn-small btn-primary" href="tweet.php?action=tweet_o&question=<?php echo $row['id'];?>">Tweet Options</a></td>
									<td><a class="btn btn-small btn-primary" href="tweet.php?action=tweet_c&question=<?php echo $row['id'];?>">Tweet comment</a></td>
									<td><a class="btn btn-small btn-primary" href="tweet_feedback.php?round=R1&question=<?php echo $row['id'];?>">Round one feedback</a></td>
									<td><a class="btn btn-small btn-primary" href="tweet_feedback.php?round=R2&question=<?php echo $row['id'];?>">Round two feedback</a></td>
								</tr>
							<?php	
									}
								}
							?>
								
							</tbody>
						</table>
					</div>
			  </div>
			</div>
		</div>


	</body>
</html>
<?php } ?>