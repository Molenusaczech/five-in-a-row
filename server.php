<?php

    
    error_reporting(0);

    function Encrypted($text) {
        $key = getenv('key');
        $string = $text;
        $pass = $key;
        $method = 'aes128';
        return openssl_encrypt($string, $method, $pass);
    
    }
    
    function Decrypted($text) {
        $key = getenv('key');
        $string = $text;
        $pass = $key;
        $method = 'aes128';
        return openssl_decrypt($string, $method, $pass);
    }
    

    function debugLog($message) {
        $myfile = fopen("log.txt", "a") or die("Unable to open file!");
        fwrite($myfile, $message."\n");
        fclose($myfile);
    }

    function getLangText($id, $lang) {
        $text = file_get_contents("lang/$lang.json");
        $text = json_decode($text, true);
        return $text[$id];
    }

    function checkWin($matchid, $symbol) {
        $json_data = file_get_contents('games.json');
        $json_data = Decrypted($json_data);
        $decoded = json_decode($json_data, true);
        $board = $decoded[$matchid]["board"];


        // vertical check
        $count = 0;
        //debugLog("vertical check symbol: $symbol");

        for ($col = 0; $col < 15; $col++) {
            
            // col check

            for ($row = 0; $row < 15; $row++) {
                if ($board[$row."/".$col] == $symbol) {
                    $count++;
                    //debugLog("vertical check count: $count");
                } else {
                    $count = 0;
                }
                if ($count >= 5) {
                    //debugLog("vertical check win $symbol");
                    return true;
                    
                }
            }

        }

        // horizontal check

        $count = 0;
        for ($row = 0; $row < 15; $row++) {
            
            // col check

            for ($col = 0; $col < 15; $col++) {
                if ($board[$row."/".$col] == $symbol) {
                    $count++;
                } else {
                    $count = 0;
                }
                if ($count == 5) {
                    return true;
                }
            }

        }

        // diagonal check 1 (left top to right bottom)

        
        $count = 0;
        for ($index = -15; $index < 15; $index++) {
            for ($y = 0; $y < 15; $y++) {
                $x = $index + $y;
                
                    if ($board[$x."/".$y] == $symbol) {
                        $count++;
                    } else {
                        $count = 0;
                    }
                    if ($count == 5) {
                        return true;
                    
                }
            }
            $count = 0;
        }


        // diagonal check 2 (left bottom to right top)

        $count = 0;
        for ($index = -15; $index < 15; $index++) {
            for ($y = 0; $y < 15; $y++) {
                $x = $index + $y;
                
                    $invX = 15 - $x;
                    $invY = $y;
                    if ($board[$invX."/".$invY] == $symbol) {
                        $count++;
                    } else {
                        $count = 0;
                    }
                    if ($count == 5) {
                        return true;
                    }
                
            }
            $count = 0;
        }

        return false;

    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function createRematch($p1id, $p2id, $p1name, $p2name) {
        $newmatchid = generateRandomString(16);
        //debugLog("createRematch matchid: $newmatchid");

        $json_data = file_get_contents('games.json');
        $json_data = Decrypted($json_data);
        //debugLog("createRematch json1: $json_data");
        $decoded = json_decode($json_data, true);
        

        $decoded[$newmatchid]["player1"] = $p2id;
        $decoded[$newmatchid]["player2"] = $p1id;
        $decoded[$newmatchid]["player1name"] = $p2name;
        $decoded[$newmatchid]["player2name"] = $p1name;
        $decoded[$newmatchid]["status"] = "swap1";
        $decoded[$newmatchid]["board"] = array();
        $decoded[$newmatchid]["p1symbol"] = "null";
        $decoded[$newmatchid]["p2symbol"] = "null";
        

        $finalJson = json_encode($decoded);
        //debugLog("createRematch json2: $finalJson");
        $finalJson = Encrypted($finalJson);
        $myfile = fopen("games.json", "w") or die("Unable to open file!");

        fwrite($myfile, $finalJson);
        fclose($myfile);

        return $newmatchid;
    }


    $matchid = $_GET["match"];
    $token = $_GET["token"];
    $login = $_GET["login"];
    $lang = $_GET["lang"];

    $json_data = file_get_contents('games.json');
    $json_data = Decrypted($json_data);
    $json_data = json_decode($json_data, true);
    $status = $json_data[$matchid]["status"];

    $user_data = file_get_contents('users.json');
    $user_data = Decrypted($user_data);
    $user_data = json_decode($user_data, true);

    if ($json_data[$matchid]["player1"] == $token) {
        $symbol = $json_data[$matchid]["p1symbol"];
        $player = 1;
    } else if ($json_data[$matchid]["player2"] == $token) {
        $symbol = $json_data[$matchid]["p2symbol"];
        $player = 2;
    } else {
        $symbol = "denied";
        $player = 0;
    }
    /*
    // test token validity
    if (strlen($token) == 64) {
        // user is logged in
        if ($token !== $user_data[$login]["token"]) {
            // token is invalid
            $symbol = "denied";
            $player = 0;
        }

    }*/

    if ($status == "waiting" || $status == "choose") {
        $playerTurn = "false";
    }

    if ($status == "swap1" || $status == "swap2" || $status == "swap3") {
        if ($player == 2) {
            $playerTurn = "false";
    } }

    if ($status == "turn1" && $player !== 1) {
        $playerTurn = "false";
    }

    if ($status == "turn2" && $player !== 2) {
        $playerTurn = "false";
    }

    if ($status == "win1" || $status == "win2" || $status == "rematch1") {
        $playerTurn = "false";
    }


    // p1 swap
    if ($player == 1) {
        if ($status == "swap1") {
            $json_data[$matchid]["status"] = "swap2";
            $symbol = "x";
        } else if ($status == "swap2") {
            $json_data[$matchid]["status"] = "swap3";
            $symbol = "o";
        } else if ($status == "swap3") {
            $json_data[$matchid]["status"] = "choose";
            $symbol = "x";
        }
    }

    if($_GET["x"] !== "" && $_GET["x"] !== null && $_GET["y"] !== "" && $_GET["y"] !== null && $symbol !== "denied" && $playerTurn !== "false") {

        $x = $_GET["x"];
        $y = $_GET["y"];


        if (!isset($json_data[$matchid]["board"][$x."/".$y])) {

        $json_data[$matchid]["board"][$x."/".$y] = $symbol;
        $json_data[$matchid]["lastlastmove"] = $json_data[$matchid]["lastmove"];
        $json_data[$matchid]["lastmove"] = $x."/".$y;

        if ($status == "turn1") {
            $json_data[$matchid]["status"] = "turn2";
        } else if($status == "turn2") {
            $json_data[$matchid]["status"] = "turn1";
        }
        

        $finalJson = json_encode($json_data);
        $finalJson = Encrypted($finalJson);
        $myfile = fopen("games.json", "w") or die("Unable to open file!");

        fwrite($myfile, $finalJson);
        fclose($myfile);

        $p1symbol = $json_data[$matchid]["p1symbol"];
        $p2symbol = $json_data[$matchid]["p2symbol"];

        $stats = file_get_contents('stats.json');
        //$stats = Decrypted($stats);
        $stats = json_decode($stats, true);
        $player1name = $json_data[$matchid]["player1name"];
        $player2name = $json_data[$matchid]["player2name"];

        if (checkWin($matchid, $p1symbol)) {
            $json_data[$matchid]["status"] = "win1";
            //debugLog("player 1 win");

            $stats[$player1name]["wins"] = $stats[$player1name]["wins"] + 1;
            $stats[$player2name]["losses"] = $stats[$player2name]["losses"] + 1;
            $stats[$player1name]["plays"] = $stats[$player1name]["plays"] + 1;
            $stats[$player2name]["plays"] = $stats[$player2name]["plays"] + 1;
            $stats[$player1name]["matchups"][$player2name]["wins"] = $stats[$player1name]["matchups"][$player2name]["wins"] + 1;
            $stats[$player2name]["matchups"][$player1name]["losses"] = $stats[$player2name]["matchups"][$player1name]["losses"] + 1;
            $stats[$player1name]["matchups"][$player2name]["plays"] = $stats[$player1name]["matchups"][$player2name]["plays"] + 1;
            $stats[$player2name]["matchups"][$player1name]["plays"] = $stats[$player2name]["matchups"][$player1name]["plays"] + 1;

        } else if (checkWin($matchid, $p2symbol)) {
            $json_data[$matchid]["status"] = "win2";
            //debugLog("player 2 win");

            $stats[$player2name]["wins"] = $stats[$player2name]["wins"] + 1;
            $stats[$player1name]["losses"] = $stats[$player1name]["losses"] + 1;
            $stats[$player1name]["plays"] = $stats[$player1name]["plays"] + 1;
            $stats[$player2name]["plays"] = $stats[$player2name]["plays"] + 1;
            $stats[$player2name]["matchups"][$player1name]["wins"] = $stats[$player2name]["matchups"][$player1name]["wins"] + 1;
            $stats[$player1name]["matchups"][$player2name]["losses"] = $stats[$player1name]["matchups"][$player2name]["losses"] + 1;
            $stats[$player1name]["matchups"][$player2name]["plays"] = $stats[$player1name]["matchups"][$player2name]["plays"] + 1;
            $stats[$player2name]["matchups"][$player1name]["plays"] = $stats[$player2name]["matchups"][$player1name]["plays"] + 1;

        } else if (count($json_data[$matchid]["board"]) == 225) {
            $json_data[$matchid]["status"] = "draw";
            //debugLog("draw");

            $stats[$player2name]["draws"] = $stats[$player2name]["draws"] + 1;
            $stats[$player1name]["draws"] = $stats[$player1name]["draws"] + 1;
            $stats[$player1name]["plays"] = $stats[$player1name]["plays"] + 1;
            $stats[$player2name]["plays"] = $stats[$player2name]["plays"] + 1;
            $stats[$player2name]["matchups"][$player1name]["draws"] = $stats[$player2name]["matchups"][$player1name]["draws"] + 1;
            $stats[$player1name]["matchups"][$player2name]["draws"] = $stats[$player1name]["matchups"][$player2name]["draws"] + 1;
            $stats[$player1name]["matchups"][$player2name]["plays"] = $stats[$player1name]["matchups"][$player2name]["plays"] + 1;
            $stats[$player2name]["matchups"][$player1name]["plays"] = $stats[$player2name]["matchups"][$player1name]["plays"] + 1;

        }
        $finalJson = json_encode($json_data);
        $finalJson = Encrypted($finalJson);
        $myfile = fopen("games.json", "w") or die("Unable to open file!");

        fwrite($myfile, $finalJson);
        fclose($myfile);


        //stat file write

        $finalJson = json_encode($stats);
        //$finalJson = Encrypted($finalJson);
        $myfile = fopen("stats.json", "w") or die("Unable to open file!");

        fwrite($myfile, $finalJson);
        fclose($myfile);

        

    }

    } else if ($_GET["symbol"] !== "" && $_GET["symbol"] !== null && $status == "choose") {




        $symbol1 = $_GET["symbol"];
        if ($_GET["symbol"] == "x") {
            $symbol2 = "o";
            $json_data[$matchid]["status"] = "turn1";
        } else {
            $symbol2 = "x";
            $json_data[$matchid]["status"] = "turn2";
        }

        $json_data[$matchid]["p1symbol"] = $symbol2;
        $json_data[$matchid]["p2symbol"] = $symbol1;
        

        $finalJson = json_encode($json_data);
        $finalJson = Encrypted($finalJson);
        $myfile = fopen("games.json", "w") or die("Unable to open file!");

        fwrite($myfile, $finalJson);
        fclose($myfile);

    } else if ($_GET["rematch"] == "true") {
        if ($status == "win1" || $status == "win2") {

            if ($player == 1) {
                $json_data[$matchid]["status"] = "rematch1";
            } else if ($player == 2) {
                $json_data[$matchid]["status"] = "rematch2";
            }

            $finalJson = json_encode($json_data);
            $finalJson = Encrypted($finalJson);
            $myfile = fopen("games.json", "w") or die("Unable to open file!");

            fwrite($myfile, $finalJson);
            fclose($myfile);

        } else if ($status == "rematch1" && $player == 2) {
            $rematchAccepted = 1;
        } else if ($status == "rematch2" && $player == 1) {
            $rematchAccepted = 1;
        }

        if ($rematchAccepted == 1) {
            $p1id = $json_data[$matchid]["player1"];
            $p2id = $json_data[$matchid]["player2"];
            $p1name = $json_data[$matchid]["player1name"];
            $p2name = $json_data[$matchid]["player2name"];
            $newmatchid = createRematch($p1id, $p2id, $p1name, $p2name);

            $json_data = file_get_contents('games.json');
            $json_data = Decrypted($json_data);
            //debugLog("createRematch json1: $json_data");
            $json_data = json_decode($json_data, true);

            $json_data[$matchid]["status"] = "redirect";
            $json_data[$matchid]["redirect"] = "game.php?match=".$newmatchid;

            $finalJson = json_encode($json_data);
            $finalJson = Encrypted($finalJson);
            $myfile = fopen("games.json", "w") or die("Unable to open file!");

            fwrite($myfile, $finalJson);
            fclose($myfile);
            die();
        }

    } else {
        
        
        $match_data = $json_data[$matchid];

        if ($player == 1) {
           $match_data["status"] = "wait";
        }

       

        if ($status == "win1") {
            if ($player == 1) {
                $match_data["status"] = "win";
            } else if ($player == 2) {
                $match_data["status"] = "lose";
            }
        }

        if ($status == "win2") {
            if ($player == 1) {
                $match_data["status"] = "lose";
            } else if ($player == 2) {
                $match_data["status"] = "win";
            }
        }

        if ($status == "rematch1") {
            if ($player == 1) {
                $match_data["status"] = "waitingforrematch";
            } else if ($player == 2) {
                $match_data["status"] = "rematchoffered";
            }
        }

        if ($status == "rematch2") {
            if ($player == 1) {
                $match_data["status"] = "rematchoffered";
            } else if ($player == 2) {
                $match_data["status"] = "waitingforrematch";
            }
        }

        if ($status == "waiting") {
            $match_data["userStatus"] = getLangText("waitingForOpponentToJoin", $lang);
        } else if ($status == "turn1") {
            if ($player == 1) {
                $match_data["userStatus"] = getLangText("yourTurn", $lang);
            } else if ($player == 2) {
                $match_data["userStatus"] = getLangText("opponentTurn", $lang);
            }
        } else if ($status == "turn2") {
            if ($player == 2) {
                $match_data["userStatus"] = getLangText("yourTurn", $lang);
            } else if ($player == 1) {
                $match_data["userStatus"] = getLangText("opponentTurn", $lang);
            }
        } else if ($status == "swap1") {
            if ($player == 1) {
                $match_data["userStatus"] = getLangText("swap1", $lang);
            } else if ($player == 2) {
                $match_data["userStatus"] = getLangText("waitingForSwap", $lang);
            }
        } else if ($status == "swap2") {
            if ($player == 1) {
                $match_data["userStatus"] = getLangText("swap2", $lang);
            } else if ($player == 2) {
                $match_data["userStatus"] = getLangText("waitingForSwap", $lang);
            }
        } else if ($status == "swap3") {
            if ($player == 1) {
                $match_data["userStatus"] = getLangText("swap3", $lang);
            } else if ($player == 2) {
                $match_data["userStatus"] = getLangText("waitingForSwap", $lang);
            }
        } else if ($status == "choose") {
            if ($player == 1) {
                $match_data["userStatus"] = getLangText("waitForChoose", $lang);
            } else if ($player == 2) {
                $match_data["userStatus"] = "";
            }
        } else if ($status == "win1" || $status == "win2" || $status == "draw") {
            
            $match_data["userStatus"] = getLangText("matchEnded", $lang);
            
        } 

        unset($match_data["player1"]);
        unset($match_data["player2"]);
        
        if ($match_data["player2name"] == "null") {
            $match_data["player2name"] = getLangText("waiting", $lang);
        }

        //Stat handler

        $stats = file_get_contents('stats.json');
        //$stats = Decrypted($stats);
        $stats = json_decode($stats, true);
        $p1name = $match_data["player1name"];
        $p2name = $match_data["player2name"];

        $match_data["stats"]["matches1"] = $stats[$p1name]["plays"];
        $match_data["stats"]["matches2"] = $stats[$p2name]["plays"];
        $match_data["stats"]["wins1"] = $stats[$p1name]["wins"];
        $match_data["stats"]["wins2"] = $stats[$p2name]["wins"];
        $match_data["stats"]["loses1"] = $stats[$p1name]["losses"];
        $match_data["stats"]["loses2"] = $stats[$p2name]["losses"];
        $match_data["stats"]["draws1"] = $stats[$p1name]["draws"];
        $match_data["stats"]["draws2"] = $stats[$p2name]["draws"];
        if ($stats[$p1name]["plays"] != 0) {
            $match_data["stats"]["winp1"] = round($stats[$p1name]["wins"] / $stats[$p1name]["plays"] * 100, 2) . "%";
        } else {
            $match_data["stats"]["winp1"] = 0;
        }

        if ($stats[$p2name]["plays"] != 0) {
            $match_data["stats"]["winp2"] = round($stats[$p2name]["wins"] / $stats[$p2name]["plays"] * 100, 2) . "%";
        } else {
            $match_data["stats"]["winp2"] = 0;
        }

        if (!isset($match_data["stats"]["matches1"])) {
            $match_data["stats"]["matches1"] = 0;
        }
        if (!isset($match_data["stats"]["matches2"])) {
            $match_data["stats"]["matches2"] = 0;
        }
        if (!isset($match_data["stats"]["wins1"])) {
            $match_data["stats"]["wins1"] = 0;
        }
        if (!isset($match_data["stats"]["wins2"])) {
            $match_data["stats"]["wins2"] = 0;
        }
        if (!isset($match_data["stats"]["loses1"])) {
            $match_data["stats"]["loses1"] = 0;
        }
        if (!isset($match_data["stats"]["loses2"])) {
            $match_data["stats"]["loses2"] = 0;
        }
        if (!isset($match_data["stats"]["draws1"])) {
            $match_data["stats"]["draws1"] = 0;
        }
        if (!isset($match_data["stats"]["draws2"])) {
            $match_data["stats"]["draws2"] = 0;
        }

        $match_data["stats"]["matchwins1"] = $stats[$p1name]["matchups"][$p2name]["wins"];
        $match_data["stats"]["matchwins2"] = $stats[$p2name]["matchups"][$p1name]["wins"];
        $match_data["stats"]["matchloses1"] = $stats[$p1name]["matchups"][$p2name]["losses"];
        $match_data["stats"]["matchloses2"] = $stats[$p2name]["matchups"][$p1name]["losses"];
        $match_data["stats"]["matchdraw1"] = $stats[$p1name]["matchups"][$p2name]["draws"];
        $match_data["stats"]["matchdraw2"] = $stats[$p2name]["matchups"][$p1name]["draws"];
        if ($stats[$p1name]["matchups"][$p2name]["plays"] != 0) {
            $match_data["stats"]["matchwinp1"] = $stats[$p1name]["matchups"][$p2name]["wins"] / $stats[$p1name]["matchups"][$p2name]["plays"] * 100 . "%";
        } else {
            $match_data["stats"]["matchwinp1"] = 0;
        }

        if ($stats[$p2name]["matchups"][$p1name]["plays"] != 0) {
            $match_data["stats"]["matchwinp2"] = $stats[$p2name]["matchups"][$p1name]["wins"] / $stats[$p2name]["matchups"][$p1name]["plays"] * 100 . "%";
        } else {
            $match_data["stats"]["matchwinp2"] = 0;
        }

        if (!isset($match_data["stats"]["matchwins1"])) {
            $match_data["stats"]["matchwins1"] = 0;
        }

        if (!isset($match_data["stats"]["matchwins2"])) {
            $match_data["stats"]["matchwins2"] = 0;
        }

        if (!isset($match_data["stats"]["matchloses1"])) {
            $match_data["stats"]["matchloses1"] = 0;
        }

        if (!isset($match_data["stats"]["matchloses2"])) {
            $match_data["stats"]["matchloses2"] = 0;
        }

        if (!isset($match_data["stats"]["matchdraw1"])) {
            $match_data["stats"]["matchdraw1"] = 0;
        }

        if (!isset($match_data["stats"]["matchdraw2"])) {
            $match_data["stats"]["matchdraw2"] = 0;
        }

    
        $final_data = json_encode($match_data);
        echo $final_data;
    }
?>

