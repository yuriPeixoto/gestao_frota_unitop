<?php

namespace App\Services;

/**
 * Serviço para geração de corpos HTML para emails
 */
class HTMLBodyService
{
   /**
    * Gera o corpo HTML para emails de cotação
    *
    * @param string $empresa
    * @param string $enderecoEmpresa
    * @param string $numeroCotacao
    * @param string|null $nomeFornecedor
    * @return string
    */
   public function generateBody($empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor = null)
   {
      $corpoDoEmail = '<!doctype html>
<html lang="pt-BR">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8" />
    <title>Cotação de Preço</title>
    <style media="all" type="text/css">
        /* -------------------------------------
        GLOBAL RESETS
        ------------------------------------- */
        body {
            font-family: Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 16px;
            line-height: 1.3;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }

        table td {
            font-family: Helvetica, sans-serif;
            font-size: 16px;
            vertical-align: top;
        }

        /* -------------------------------------
        BODY & CONTAINER
        ------------------------------------- */
        body {
            background-color: #f4f5f6;
            margin: 0;
            padding: 0;
        }

        .body {
            background: url("https://www.unitopconsultoria.com.br/img/bg-azul.jpg") no-repeat center;
            background-size: cover;
            padding: 20px;
            width: 100%;
        }

        .container {
            margin: 0 auto !important;
            max-width: 600px;
            padding: 0;
            padding-top: 24px;
            width: 600px;
        }

        .content {
            box-sizing: border-box;
            display: block;
            margin: 0 auto;
            max-width: 600px;
            padding: 0;
        }

        /* -------------------------------------
        HEADER, FOOTER, MAIN
        ------------------------------------- */
        .main {
            background: #ffffff;
            border: 1px solid #eaebed;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.3);
            border-radius: 16px;
            width: 100%;
        }

        .wrapper {
            box-sizing: border-box;
            padding: 24px;
        }

        .footer {
            clear: both;
            padding-top: 24px;
            text-align: center;
            width: 100%;
        }

        .footer td,
        .footer p,
        .footer span,
        .footer a {
            color: #fff;
            font-size: 16px;
            text-align: center;
        }

        .footer span {
            color: #333;
        }

        .footer td {
            background: #fff;
            padding: 8px;
            color: #333;
            margin-top: 12px;
            box-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;
        }

        /* -------------------------------------
        TYPOGRAPHY
        ------------------------------------- */
        p {
            font-family: Helvetica, sans-serif;
            font-size: 16px;
            font-weight: normal;
            margin: 0;
            margin-bottom: 16px;
        }

        a {
            color: #0867ec;
            text-decoration: underline;
        }

        /* -------------------------------------
        BUTTONS
        ------------------------------------- */
        .btn {
            box-sizing: border-box;
            min-width: 100% !important;
            width: 100%;
        }

        .btn > tbody > tr > td {
            padding-bottom: 16px;
        }

        .btn table {
            width: auto;
        }

        .btn table td {
            background-color: #ffffff;
            border-radius: 4px;
            text-align: center;
        }

        .btn a {
            background-color: #ffffff;
            border: solid 2px #0867ec;
            border-radius: 4px;
            box-sizing: border-box;
            color: #0867ec;
            cursor: pointer;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            padding: 12px 24px;
            text-decoration: none;
            text-transform: capitalize;
        }

        .btn-primary table td {
            background-color: #0867ec;
        }

        .btn-primary a {
            background-color: #0867ec;
            border-color: #0867ec;
            color: #ffffff;
        }

        @media all {
            .btn-primary table td:hover {
                background-color: #033a87 !important;
            }
            .btn-primary a:hover {
                background-color: #033a87 !important;
                border-color: #033a87 !important;
            }
        }

        /* -------------------------------------
        OTHER STYLES THAT MIGHT BE USEFUL
        ------------------------------------- */
        .last {
            margin-bottom: 0;
        }

        .first {
            margin-top: 0;
        }

        .align-center {
            text-align: center;
        }

        .align-right {
            text-align: right;
        }

        .align-left {
            text-align: left;
        }

        .text-link {
            color: #0867ec !important;
            text-decoration: underline !important;
        }

        .bold {
            font-weight: bold;
            color: #333 !important;
        }

        .clear {
            clear: both;
        }

        .mt0 {
            margin-top: 0;
        }

        .mb0 {
            margin-bottom: 0;
        }

        .preheader {
            color: transparent;
            display: none;
            height: 0;
            max-height: 0;
            max-width: 0;
            opacity: 0;
            overflow: hidden;
            mso-hide: all;
            visibility: hidden;
            width: 0;
        }

        .powered-by a {
            text-decoration: none;
        }

        /* -------------------------------------
        RESPONSIVE AND MOBILE FRIENDLY STYLES
        ------------------------------------- */
        @media only screen and (max-width: 640px) {
            .main p,
            .main td,
            .main span {
                font-size: 16px !important;
            }
            .wrapper {
                padding: 8px !important;
            }
            .content {
                padding: 0 !important;
            }
            .container {
                padding: 0 !important;
                padding-top: 8px !important;
                width: 100% !important;
            }
            .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }
            .btn table {
                max-width: 100% !important;
                width: 100% !important;
            }
            .btn a {
                font-size: 16px !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }

        /* -------------------------------------
        PRESERVE THESE STYLES IN THE HEAD
        ------------------------------------- */
        @media all {
            .ExternalClass {
                width: 100%;
            }
            .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
                line-height: 100%;
            }
            .apple-link a {
                color: inherit !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                text-decoration: none !important;
            }
            #MessageViewBody a {
                color: inherit;
                text-decoration: none;
                font-size: inherit;
                font-family: inherit;
                font-weight: inherit;
                line-height: inherit;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td>&nbsp;</td>
            <td class="container">
                <div class="content">
                    <!-- START CENTERED WHITE CONTAINER -->
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main">
                        <!-- START MAIN CONTENT AREA -->
                        <tr>
                            <td class="wrapper">
                                <p>Olá!</p>
                                <p>Gostaríamos de informar que temos uma nova cotação disponível para você na ' . $empresa . '. Por favor, preencha o formulário para que possamos dar continuidade.</p>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                    <tbody>
                                        <tr>
                                            <td align="left">
                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                        <tr>
                                                            <td><a href="https://homologacao.unitopconsultoria.com.br/cotacao/?cotacao=' . $numeroCotacao . '&nomeFornecedor=' . $nomeFornecedor . '" target="_blank">Abrir Cotação</a></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p></p>
                                <p>Obrigado!</p>
                            </td>
                        </tr>
                        <!-- END MAIN CONTENT AREA -->
                    </table>
                    <!-- START FOOTER -->
                    <div class="footer">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="content-block">
                                    <span class="apple-link">' . $enderecoEmpresa . '</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="content-block powered-by">
                                    Desenvolvido por <a href="https://www.unitopconsultoria.com.br" class="bold">Unitop Sistemas e Consultoria</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- END FOOTER -->
                    <!-- END CENTERED WHITE CONTAINER -->
                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>';

      return mb_convert_encoding($corpoDoEmail, 'UTF-8');
   }

   /**
    * Método estático para compatibilidade com código legado
    */
   public static function generateBodyStatic($empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor = null)
   {
      $service = new static();
      return $service->generateBody($empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor);
   }
}
