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


    $matchid = $_GET["match"];
    $token = $_GET["token"];
    $json_data = file_get_contents('games.json');
    $json_data = Decrypted($json_data);
    $json_data = json_decode($json_data, true);
    $status = $json_data[$matchid]["status"];

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

    if ($status == "win1" || $status == "win2") {
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

        if (checkWin($matchid, $p1symbol)) {
            $json_data[$matchid]["status"] = "win1";
            debugLog("player 1 win");
        } else if (checkWin($matchid, $p2symbol)) {
            $json_data[$matchid]["status"] = "win2";
            debugLog("player 2 win");
        } else if (count($json_data[$matchid]["board"]) == 225) {
            $json_data[$matchid]["status"] = "draw";
            debugLog("draw");
        }
        $finalJson = json_encode($json_data);
        $finalJson = Encrypted($finalJson);
        $myfile = fopen("games.json", "w") or die("Unable to open file!");

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

        if ($status == "waiting") {
            $match_data["userStatus"] = "Waiting for opponent to join";
        } else if ($status == "turn1") {
            if ($player == 1) {
                $match_data["userStatus"] = "Your turn";
            } else if ($player == 2) {
                $match_data["userStatus"] = "Opponent's turn";
            }
        } else if ($status == "turn2") {
            if ($player == 2) {
                $match_data["userStatus"] = "Your turn";
            } else if ($player == 1) {
                $match_data["userStatus"] = "Opponent's turn";
            }
        } else if ($status == "swap1") {
            if ($player == 1) {
                $match_data["userStatus"] = "Place your first SWAP symbol (X)";
            } else if ($player == 2) {
                $match_data["userStatus"] = "Wait for your opponent to finish SWAP";
            }
        } else if ($status == "swap2") {
            if ($player == 1) {
                $match_data["userStatus"] = "Place your second SWAP symbol (O)";
            } else if ($player == 2) {
                $match_data["userStatus"] = "Wait for your opponent to finish SWAP";
            }
        } else if ($status == "swap3") {
            if ($player == 1) {
                $match_data["userStatus"] = "Place your third (and last) SWAP symbol (X)";
            } else if ($player == 2) {
                $match_data["userStatus"] = "Wait for your opponent to finish SWAP";
            }
        } else if ($status == "choose") {
            if ($player == 1) {
                $match_data["userStatus"] = "Wait for your opponent to choose a symbol";
            } else if ($player == 2) {
                $match_data["userStatus"] = "";
            }
        } else if ($status == "win1" || $status == "win2" || $status == "draw") {
            
            $match_data["userStatus"] = "Match has ended";
            
        } 

        
        
    
        $final_data = json_encode($match_data);
        echo $final_data;
    }
?>

