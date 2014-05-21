<?php
/**
 * Created by PhpStorm.
 * User: Bob Nicolaus
 * Date: 4/20/14
 * Time: 7:03 PM
 */

// Game states
define("GAME_START", 0);
define("GAME_PLAY", 1);
define("GAME_WIN", 2);
define("GAME_LOSE", 3);
define("GAME_OVER", 4);

// Game tile
define("PLAYER", "X");
define("COMPUTER", "O");

// Globals
$gMarks = [];

session_start();
StartGame();

// Check to see if the user is starting a new game
if ($_POST['btnNewGame'] != "")
{

    // Check to see if there is an on going game
    if ($_SESSION['gGameState'] != GAME_START)
    {
        EndGame();
        $GLOBALS['gGameState'] = GAME_START;
    }

    StartGame();
}

if ($_POST['btnMove'] != "")
{
    $GLOBALS['gGameState'] = $_SESSION['gGameState'];
    $GLOBALS['gBoard'] = $_SESSION['gBoard'];
    $GLOBALS['gDifficulty'] = $_SESSION['gDifficulty'];
}
?>

<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
    <html>
        <head>
            <title>Tic-Tac-Toe</title>
        </head>
        <body>
            <form action="index.php" method="post">
                <input type="hidden" name="turn" value="<? print($turn) ?>">
                <div align="center">
                    <p><H1>Tic-Tac-Toe</H1>You'll never win!</p>
                    <BR>
                    <?php
                        // Render the game
                        Render();
                    ?>
                    <BR><BR><input type="submit" name="btnNewGame" value="Try again?">
                </div>
            </form>
        </body>
    </html>

<?php
function Render()
{
    switch($GLOBALS['gGameState'])
    {

        case GAME_PLAY:
        {
            // Get the move if the user made one
            if ($_POST['btnMove'] != "")
            {
                $GLOBALS['gBoard'][$_POST['btnMove']] = PLAYER;
                $_SESSION['gBoard'] = $GLOBALS['gBoard'];
            }

            // Check for a win
            if (CheckWin() == PLAYER)
            {
                $GLOBALS['gGameState'] = GAME_WIN;
                Render();
                return;
            }

            // Check to see if the board is full
            if (CheckFull() == 1)
            {
                $GLOBALS['gGameState'] = GAME_OVER;
                Render();
                return;
            }

            // Compute the computer's move if we can still move
            if ($GLOBALS['gGameState'] == GAME_PLAY && $_POST['btnMove'] != "")
            {
                if (!ComputerMove())
                    ComputerRandomMove();
            }

            // Check for a win
            if (CheckWin() == COMPUTER)
            {
                $GLOBALS['gGameState'] = GAME_LOSE;
                Render();
                return;
            }

            // Check to see if the board is full
            if (CheckFull() == 1)
            {
                $GLOBALS['gGameState'] = GAME_OVER;
                Render();
                return;
            }

            // Draw the board
            DrawBoard();
            break;
        }

        case GAME_WIN:
        {
            $GLOBALS['gBgColor'] = "yellow";
            DrawBoard();
            EndGame();
            printf("<br><br>Okay! You win!");
            break;
        }

        case GAME_LOSE:
        {
            $GLOBALS['gBgColor'] = "red";
            DrawBoard();
            EndGame();
            printf("<br><br>You lose! Good day, Sir!");
            break;
        }

        case GAME_OVER:
        {
            DrawBoard();
            EndGame();
            printf("<br><br>Aww, it's a draw!");
            break;
        }
    }

    // Update our game state
    $_SESSION['gGameState'] = $GLOBALS['gGameState'];
}

function StartGame()
{
    if ($GLOBALS['gGameState'] == GAME_START)
    {
        $GLOBALS['gGameState'] = GAME_PLAY;
    }

    //session_start();
    $turn = $_SESSION['turn'];

    if (!isset($turn))
    {
        $turn = 1;
        $GLOBALS['gBoard'] = array("", "", "", "", "", "", "", "", "");
        $_SESSION['gGameState'] = $GLOBALS['gGameState'];
        $_SESSION['gBoard'] = $GLOBALS['gBoard'];
        $_SESSION['turn'] = $turn;
    }

    // Retrieve the board state
    $GLOBALS['gBoard'] = $_SESSION['gBoard'];
}

function EndGame()
{
    $GLOBALS['gGameState'] = GAME_OVER;

    unset($GLOBALS['gBoard']);
    unset($GLOBALS['gGameState']);
    unset($turn);

    unset($_SESSION['gBoard']);
    unset($_SESSION['gGameState']);
    unset($_SESSION['turn']);

    session_destroy();
}

function DrawBoard()
{
    // Start the table
    print("<table border=0 cellpadding=0 cellspacing=0>");

    $iLoop = 0;
    for ($iRow = 0; $iRow < 5; $iRow++)
    {
        print("<tr>\n");
        for ($iCol = 0; $iCol < 5; $iCol++)
        {
            if ($iRow == 1 || $iRow == 3)
            {
                print("<td width=\"12\" height=\"5\" align=\"center\" valign=\"middle\" bgcolor=\"#000000\">&nbsp;</td>\n");
            }
            else
            {
                if ($iCol == 1 || $iCol == 3)
                {
                    print("<td width=\"18\" height=\"115\" align=\"center\" valign=\"middle\" bgcolor=\"#000000\">&nbsp;</td>\n");
                }
                else
                {
                    if (!in_array($iLoop, $GLOBALS['gMarks']))
                        print("<td width=\"115\" height=\"115\" align=\"center\" valign=\"middle\">");
                    else
                        print("<td width=\"115\" height=\"115\" align=\"center\" valign=\"middle\" bgcolor=" . $GLOBALS['gBgColor'] . ">");

                    if ($GLOBALS['gBoard'][$iLoop] != "")
                    {
                        print("<H1>" . $GLOBALS['gBoard'][$iLoop] . "</H1>");
                    }
                    else
                    {
                        print("<input type=\"submit\" name=\"btnMove\" value=\"" . $iLoop . "\">");
                    }
                    print("</td>\n");
                    $iLoop++;
                }
            }
        }
        print("</tr>\n");
    }

    // End the table
    print("</table>");
}

function CheckFull()
{
    $GLOBALS['gGameState'] = GAME_OVER;

    for ($iLoop = 0; $iLoop < count($GLOBALS['gBoard']); $iLoop++)
    {
        if ($GLOBALS['gBoard'][$iLoop] == "")
        {
            $GLOBALS['gGameState'] = GAME_PLAY;
            return 0;
        }
    }

    return 1;
}

function CheckWin()
{
    $player = 1;

    while ($player <= 2)
    {
        if ($player == 1)
        {
            $tile = COMPUTER;
        }
        else
        {
            $tile = PLAYER;
        }

        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][1] == $tile && $GLOBALS['gBoard'][2] == $tile)
        {
            $GLOBALS['gMarks'] = [0, 1, 2];
            return $tile;
        }

        if ($GLOBALS['gBoard'][3] == $tile && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][5] == $tile)
        {
            $GLOBALS['gMarks'] = [3, 4, 5];
            return $tile;
        }

        if ($GLOBALS['gBoard'][6] == $tile && $GLOBALS['gBoard'][7] == $tile && $GLOBALS['gBoard'][8] == $tile)
        {
            $GLOBALS['gMarks'] = [6, 7, 8];
            return $tile;
        }

        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][3] == $tile && $GLOBALS['gBoard'][6] == $tile)
        {
            $GLOBALS['gMarks'] = [0, 3, 6];
            return $tile;
        }

        if ($GLOBALS['gBoard'][1] == $tile && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][7] == $tile)
        {
            $GLOBALS['gMarks'] = [1, 4, 7];
            return $tile;
        }

        if ($GLOBALS['gBoard'][2] == $tile && $GLOBALS['gBoard'][5] == $tile && $GLOBALS['gBoard'][8] == $tile)
        {
            $GLOBALS['gMarks'] = [2, 5, 8];
            return $tile;
        }

        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][8] == $tile)
        {
            $GLOBALS['gMarks'] = [0, 4, 8];
            return $tile;
        }

        if ($GLOBALS['gBoard'][2] == $tile && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][6] == $tile)
        {
            $GLOBALS['gMarks'] = [2, 4, 6];
            return $tile;
        }

        $player++;
    }
}

function ComputerRandomMove()
{
    $computerMove = -1;

    srand((double) microtime() * 1000000);

    while ($computerMove == -1)
    {
        if ($GLOBALS['gBoard'][4] != "")
        {
            $test = rand(0, 3);

            switch ($test)
            {
                case 0:
                    $test = 0;
                    break;
                case 1:
                    $test = 2;
                    break;
                case 2:
                    $test = 6;
                    break;
                case 3:
                    $test = 8;
                    break;
            }
        }
        else
            $test = rand(0, 8);

        if ($GLOBALS['gBoard'][$test] == "")
        {
            $computerMove = $test;
            $GLOBALS['gBoard'][$computerMove] = COMPUTER;
            $_SESSION['gBoard'] = $GLOBALS['gBoard'];
        }
    }
}

function ComputerMove()
{
    $computerMove = -1;
    $player = 1;

    while ($player <= 2 && $computerMove == -1)
    {
        if ($player == 1)
        {
            $tile = COMPUTER;
        }
        else
        {
            $tile = PLAYER;
        }

        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][1] == $tile && $GLOBALS['gBoard'][2] == '')
            $computerMove = 2;
        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][1] == '' && $GLOBALS['gBoard'][2] == $tile)
            $computerMove = 1;
        if ($GLOBALS['gBoard'][0] == '' && $GLOBALS['gBoard'][1] == $tile && $GLOBALS['gBoard'][2] == $tile)
            $computerMove = 0;
        if ($GLOBALS['gBoard'][3] == $tile && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][5] == '')
            $computerMove = 5;
        if ($GLOBALS['gBoard'][3] == $tile && $GLOBALS['gBoard'][4] == '' && $GLOBALS['gBoard'][5] == $tile)
            $computerMove = 4;
        if ($GLOBALS['gBoard'][3] == '' && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][5] == $tile)
            $computerMove = 3;
        if ($GLOBALS['gBoard'][6] == $tile && $GLOBALS['gBoard'][7] == $tile && $GLOBALS['gBoard'][8] == '')
            $computerMove = 8;
        if ($GLOBALS['gBoard'][6] == $tile && $GLOBALS['gBoard'][7] == '' && $GLOBALS['gBoard'][8] == $tile)
            $computerMove = 7;
        if ($GLOBALS['gBoard'][6] == '' && $GLOBALS['gBoard'][7] == $tile && $GLOBALS['gBoard'][8] == $tile)
            $computerMove = 6;
        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][3] == $tile && $GLOBALS['gBoard'][6] == '')
            $computerMove = 6;
        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][3] == '' && $GLOBALS['gBoard'][6] == $tile)
            $computerMove = 3;
        if ($GLOBALS['gBoard'][0] == '' && $GLOBALS['gBoard'][3] == $tile && $GLOBALS['gBoard'][6] == $tile)
            $computerMove = 0;
        if ($GLOBALS['gBoard'][1] == $tile && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][7] == '')
            $computerMove = 7;
        if ($GLOBALS['gBoard'][1] == $tile && $GLOBALS['gBoard'][4] == '' && $GLOBALS['gBoard'][7] == $tile)
            $computerMove = 4;
        if ($GLOBALS['gBoard'][1] == '' && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][7] == $tile)
            $computerMove = 1;
        if ($GLOBALS['gBoard'][2] == $tile && $GLOBALS['gBoard'][5] == $tile && $GLOBALS['gBoard'][8] == '')
            $computerMove = 8;
        if ($GLOBALS['gBoard'][2] == $tile && $GLOBALS['gBoard'][5] == '' && $GLOBALS['gBoard'][8] == $tile)
            $computerMove = 5;
        if ($GLOBALS['gBoard'][2] == '' && $GLOBALS['gBoard'][5] == $tile && $GLOBALS['gBoard'][8] == $tile)
            $computerMove = 2;
        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][8] == '')
            $computerMove = 8;
        if ($GLOBALS['gBoard'][0] == $tile && $GLOBALS['gBoard'][4] == '' && $GLOBALS['gBoard'][8] == $tile)
            $computerMove = 4;
        if ($GLOBALS['gBoard'][0] == '' && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][8] == $tile)
            $computerMove = 0;
        if ($GLOBALS['gBoard'][2] == $tile && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][6] == '')
            $computerMove = 6;
        if ($GLOBALS['gBoard'][2] == $tile && $GLOBALS['gBoard'][4] == '' && $GLOBALS['gBoard'][6] == $tile)
            $computerMove = 4;
        if ($GLOBALS['gBoard'][2] == '' && $GLOBALS['gBoard'][4] == $tile && $GLOBALS['gBoard'][6] == $tile)
            $computerMove = 2;

        if($computerMove != -1)
            break;
        else
            $player++;
    }

    if ($computerMove == -1 && $GLOBALS['gBoard'][4] == '')
        $computerMove = 4;

    if ($computerMove != -1)
    {
        $GLOBALS['gBoard'][$computerMove] = COMPUTER;
        $_SESSION['gBoard'] = $GLOBALS['gBoard'];
        return true;
    }

    return false;
}
?>

