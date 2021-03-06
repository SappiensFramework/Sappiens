<?php
/*

    Sappiens Framework
    Copyright (C) 2014, BRA Consultoria

    Website do autor: www.braconsultoria.com.br/sappiens
    Email do autor: sappiens@braconsultoria.com.br

    Website do projeto, equipe e documentação: www.sappiens.com.br
   
    Este programa é software livre; você pode redistribuí-lo e/ou
    modificá-lo sob os termos da Licença Pública Geral GNU, conforme
    publicada pela Free Software Foundation, versão 2.

    Este programa é distribuído na expectativa de ser útil, mas SEM
    QUALQUER GARANTIA; sem mesmo a garantia implícita de
    COMERCIALIZAÇÃO ou de ADEQUAÇÃO A QUALQUER PROPÓSITO EM
    PARTICULAR. Consulte a Licença Pública Geral GNU para obter mais
    detalhes.
 
    Você deve ter recebido uma cópia da Licença Pública Geral GNU
    junto com este programa; se não, escreva para a Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
    02111-1307, USA.

    Cópias da licença disponíveis em /Sappiens/_doc/licenca

*/

//session_start();
if(\filter_input(\INPUT_GET, 'modos')){
    \define("MODULO", "Render");
} else {
    \define("MODULO", "ArquivoDownload");
}

require '../Config.php';

$con = \Zion\Banco\Conexao::conectar();
$manipulaArquivo = new \Zion\Arquivo\ManipulaArquivo();

$modo = \filter_input(\INPUT_GET, 'modo');
$thumb = \filter_input(\INPUT_GET, 'thumb');
$uploadCod = (int) \filter_input(\INPUT_GET, 'uid'); 

if ($modo != 'download' and $modo != 'ver') {
    exit("Modo de Visualização inválido");
}

$qb = $con->qb();

//Buscando mais informações
if($modulo === "Render"){
    
    $qb->select('*')
       ->from('_upload', 'a')
       ->innerJoin('a', '_modulo', 'b', 'a.moduloCod = b.moduloCod')
       ->where($qb->expr()->eq('uploadCod', ':uploadCod'))
       ->andWhere($qb->expr()->eq('moduloNome', $qb->expr()->literal('Post')))
       ->setParameter('uploadCod', $uploadCod, \PDO::PARAM_INT);

    $sqlArquivo = $qb;

} else {

    $qb->select('*')
       ->from('_upload', 'a')
       ->where($qb->expr()->eq('uploadCod', ':uploadCod'))
       ->setParameter('uploadCod', $uploadCod, \PDO::PARAM_INT);

    $sqlArquivo = $qb;
}
try {
    $dadosArquivo = $con->execLinha($sqlArquivo);
} catch (Exception $e) {
    exit($e->getMessage());
}

$arquivo = SIS_DIR_BASE . 'Storage/' . \str_replace('-', '/', $dadosArquivo['uploaddatacadastro']) . '/' . $dadosArquivo['uploadnomefisico'];
$arquivoThumb = SIS_DIR_BASE . 'Storage/' . \str_replace('-', '/', $dadosArquivo['uploaddatacadastro']) . '/tb/' . $dadosArquivo['uploadnomefisico'];

if (!file_exists($arquivo)) {
    exit("Arquivo não encontrado");
}

$extensaoAtual = \strtolower($manipulaArquivo->extenssaoArquivo($dadosArquivo['uploadnomeoriginal']));

if ($modo == 'ver') { //Visualizar icone do arquivo, se for imagem mostrar ela mesma
    //Extenssões que é possivel ele ver
    $extensoesVer = ['jpg', 'jpeg', 'gif', 'png'];

    if (\in_array($extensaoAtual, $extensoesVer)) {
        \header('Content-Type: image/' . $extensaoAtual);
        \readfile((!$thumb) ? $arquivo : $arquivoThumb);
    } else {
        //Caminho para Uma Imagem Padrão
        \header('Content-Type: image/gif');
        \readfile(SIS_DIR_BASE . 'Arquivos/logo_exemplo.jpg');
    }
} else { //Download do Arquivo
    //Atualiza Numero de Dowloads
    try {
        $con->executar("UPDATE _upload SET uploadDownloads = (uploadDownloads +1) WHERE uploadCod = $uploadCod");
    } catch (\Exception $e) {
        
    }

    \set_time_limit(0);

    \header("Pragma: public");
    \header("Expires: 0");
    \header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    \header("Cache-Control: private", false);
    \header("Content-Disposition: attachment; filename=\"" . $dadosArquivo['uploadnomeoriginal'] . "\";");
    \header("Content-Transfer-Encoding: binary");
    \header("Content-Length: " . filesize($arquivo));

    \readfile("$arquivo") or die("Arquivo não encontrado.");
}
