<?php
/**
*
*    Sappiens Framework
*    Copyright (C) 2014, BRA Consultoria
*
*    Website do autor: www.braconsultoria.com.br/sappiens
*    Email do autor: sappiens@braconsultoria.com.br
*
*    Website do projeto, equipe e documentação: www.sappiens.com.br
*   
*    Este programa é software livre; você pode redistribuí-lo e/ou
*    modificá-lo sob os termos da Licença Pública Geral GNU, conforme
*    publicada pela Free Software Foundation, versão 2.
*
*    Este programa é distribuído na expectativa de ser útil, mas SEM
*    QUALQUER GARANTIA; sem mesmo a garantia implícita de
*    COMERCIALIZAÇÃO ou de ADEQUAÇÃO A QUALQUER PROPÓSITO EM
*    PARTICULAR. Consulte a Licença Pública Geral GNU para obter mais
*    detalhes.
* 
*    Você deve ter recebido uma cópia da Licença Pública Geral GNU
*    junto com este programa; se não, escreva para a Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
*    02111-1307, USA.
*
*    Cópias da licença disponíveis em /Sappiens/_doc/licenca
*
*/

namespace App\Ext\Remoto\ValidaSenha;

use Zion\Tratamento\Tratamento;
use App\Accounts\Login\LoginClass;

class ValidaSenha
{

    public function validaSenhaUsuario($usuarioSenha)
    {

        try {
            
            $loginCLass = new LoginClass();

            if(!isset($_SESSION['usuarioCod'])){
                throw new \Exception("0x001");
            }

            $valida = $loginCLass->validaSenhaUsuario($_SESSION['usuarioCod'], $usuarioSenha);

            return \json_encode(array('sucesso' => 'true', 'retorno' => ($valida ? 'true' : 'false')));
            
        } catch (\Exception $e) {
            $tratar = Tratamento::instancia();
            return \json_encode(array('sucesso' => 'false', 'retorno' => $tratar->texto()->trata($e->getMessage())));
        }
    }

}
