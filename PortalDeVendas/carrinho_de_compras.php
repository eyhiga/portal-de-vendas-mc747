<?php
try {

    include("wsdl.php");

    session_start("sessao");

    if(!isset($_SESSION['cpf']))
    {
        header('Location: login.php');
    }
    else
    {
        $action = $_GET["action"];

        if($action == "adicionar")
        {
            if (!isset($_SESSION["carrinho"]) || $_SESSION["carrinho"] == "") 
            {
                $_SESSION["carrinho"] = array();
            }

            $prodID = $_GET["prodID"];

            if(!isset($_SESSION["carrinho"][$prodID]) 
                || $_SESSION["carrinho"][$prodID] == "")
            {
                $_SESSION["carrinho"][$prodID] = array();

                // componente 01 - estoque    
                $client = new SoapClient($wsdlComp01);
                $resultComp01 = $client->ReturnProductInfo(
                    array("ID" => "$prodID"));

                // componente 03 - informacoes produto
                $client = new SoapClient($wsdlComp03);
                $resultComp03 = $client->exibeDetalhesID($prodID);

                $_SESSION["carrinho"][$prodID]["id"] = $prodID;
                $_SESSION["carrinho"][$prodID]["nome"] = $resultComp03[1];
                $_SESSION["carrinho"][$prodID]["qtd"] = 1;
                $_SESSION["carrinho"][$prodID]["preco"] = $resultComp01->ReturnProductInfoResult->Price;   
                $_SESSION["carrinho"][$prodID]["peso"] = $resultComp03[7];
                $_SESSION["carrinho"][$prodID]["volume"] = intval($resultComp03[8]) * intval($resultComp03[9]) * intval($resultComp03[10]); 
            }
            else
            {
                $_SESSION["carrinho"][$prodID]["qtd"] = intval($_SESSION["carrinho"][$prodID]["qtd"]) + 1;
            }
        }
        else if($action == "atualizar")
        {
            $prodID = $_GET["prodID"];
            $qtd = $_POST["qtd".$prodID];
            
            if(intval($qtd) > 0)
            {
                $_SESSION["carrinho"][$prodID]["qtd"] = $qtd;
            }
            else
            {
                unset($_SESSION["carrinho"][$prodID]);
            }

        }
        else if($action == "frete")
        {
            $client = new SoapClient($wsdlComp09);
            $resultComp09 = $client->ReturnProductInfo(
                array("ID" => "$prodID"));
        }

        if(!empty($_SESSION["carrinho"]))
        {
            // echo "<form id=\"frmCarrinho\" action=\"\" method=\"post\">";
            echo "<table>";
            echo "<tr>";
            echo "<td><b>Produto</b></td>";
            echo "<td><b>Quantidade</b></td>";
            echo "<td><b>Preco</b></td>";
            echo "<td><b>Total</b></td>";
            //echo "<td><a href=\"carrinho_de_compras.php?action=atualizar\">Atualizar</a></td>";
            echo "</tr>";

            $total = intval(0);
            foreach($_SESSION["carrinho"] as $produto)
            {
                echo "<tr>";
                echo "<td>".$produto["id"]." - ".$produto["nome"]."</td>";
                echo "<td>
                    <form id=\"frmQtd".$produto["id"]."\" method=\"post\" action=\"carrinho_de_compras.php?action=atualizar&prodID=".$produto["id"]."\">
                    <input id=\"qtd".$produto["id"]."\" name=\"qtd".$produto["id"]."\"type=\"text\" value=\"".$produto["qtd"]."\">
                    <input type=\"submit\" id=\"btnQtd".$produto["id"]."\" value=\"Atualizar\">
                    </form>
                    </td>";
                echo "<td>".$produto["preco"]."</td>";
                $total_produto = intval($produto["qtd"]) * 
                    intval($produto["preco"]);
                $total = intval($total) + intval($total_produto);
                echo "<td>".$total_produto."</td>";
                echo "<tr>";
            }

            echo "<tr>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td><b>Total</b></td>";
            echo "<td><b>".$total."</b></td>";
            echo "</tr>";

            echo "</table>";

            echo "<form id=\"frmFrete\" method=\"post\" action=\"carrinho_de_compras?action=frete\">";
            echo "<table>";
            echo "<tr>";
            echo "<td>Calcular frete: </td>";
            echo "<td><input id=\"cep\" name=\"cep\" type=\"text\" maxlength=\"8\"></td>";
            echo "<td><input type=\"submit\" id=\"btnFrete\" value=\"Calcular\"></td>";
            echo "<td></td>";
            echo "</tr>";
            echo "</table>";
            //echo "</form>";
        }
        else
        {
            echo "Carrinho vazio";
        }
    }

} catch (Exception $e) {
    echo "Exception: ";
    echo $e->getMessage();
}
?>
