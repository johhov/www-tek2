<?php
	
	session_start();
	require_once("/var/www/wwwtek2/db.php");

	$sql = 'SELECT * FROM projects WHERE status LIKE "submitted" OR status like "reworked"';
	$stmt = $db->prepare($sql);
	$stmt->execute();

	if(file_exists('../Alle_Bachelor_Prosjekt.zip')) 
	{ 
		echo "unlinking";
		unlink('../Alle_Bachelor_Prosjekt.zip'); 
	}
	
	$zip = new ZipArchive;
	$res = $zip->open("../Alle_Bachelor_Prosjekt.zip", ZipArchive::CREATE);

	if($res === TRUE)
	{
		echo "<br /> started ceating <br />";
		foreach($stmt->fetchAll() as $row)
		{
			$zip->addEmptyDir($row['title']);
			$zip->addFromString($row['title']."/Beskrivelse.txt", $row['description']);
			
			$sql = 'SELECT * FROM documents WHERE owner LIKE ?';
			$documents =  $db->prepare ($sql);
			$documents->execute(array($row['owner']));

			if($documents->rowCount!=0)
			{
				$temp = $documents->fetchAll();
				foreach($temp as $attachment)
				{
					$zip->addFromString($row['title']. "/". $attachment['name'], $attachment['content']);
				}

				$documents->closeCursor();
			}
		}
	}

	//echo "is going to close now!!! <br />";
	if($zip->close())
	{
		echo "download";
		header ('Content-type: application/zip');		// Set the correct mime type
		header ("Content-Disposition: attachment; filename=Alle_Bachelor_Prosjekt.zip");
		header ('Location: ../Alle_Bachelor_Prosjekt.zip');
	}
	
	
?>