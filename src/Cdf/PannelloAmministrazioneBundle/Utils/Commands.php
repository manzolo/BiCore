<?php

namespace Cdf\PannelloAmministrazioneBundle\Utils;

use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Cdf\PannelloAmministrazioneBundle\Utils\Utility;

class Commands
{
    /* @var $apppaths ProjectPath */

    private ProjectPath $apppaths;
    /* @var $pammutils Utility */
    private Utility $pammutils;

    public function __construct(ProjectPath $projectpath, Utility $pautils)
    {
        $this->apppaths = $projectpath;
        $this->pammutils = $pautils;
    }

    /**
     * @codeCoverageIgnoreStart
     *
     * @return array<mixed>
     * @throws \Exception
     */
    public function getVcs(): array
    {
        $command = "";
        $fs = new Filesystem();

        $projectDir = $this->apppaths->getRootPath();
        if ($fs->exists($projectDir . DIRECTORY_SEPARATOR . '.svn')) {
            $command = 'svn update';
        }
        if ($fs->exists($projectDir . DIRECTORY_SEPARATOR . '.git')) {
            $command = 'git pull';
        }
        if (!$command) {
            throw new Exception('Vcs non trovato', 100);
        }

        return $this->pammutils->runCommand($command, $projectDir);
    }

    /**
     *
     * @codeCoverageIgnoreEnd
     *
     * @return array<mixed>
     */
    public function generateEntity(string $wbFile): array
    {
        $command = 'pannelloamministrazione:generateormentities';
        $result = $this->pammutils->runSymfonyCommand($command, array('mwbfile' => $wbFile));

        if (0 != $result['errcode']) {
            return array(
                'errcode' => -1,
                'command' => $command,
                'message' => 'Errore nel comando:' . $command . ';' . $result['message'],
            );
        }

        return array(
            'errcode' => 0,
            'command' => $command,
            'message' => 'Eseguito comando:' . $command . ';' . $result['message'],);
    }

    /**
     *
     * @return array<mixed>
     */
    public function generateFormCrud(string $entityform, bool $generatemplate)
    {
        // check if some item already exist, and it interrupts the execution if any
        $pannelloamministrazioneentity = $entityform;
        /* @var $fs Filesystem */

        $entityform = $pannelloamministrazioneentity;
        $projectname = "";
        $resultchk = $this->checkFormCrud($entityform, $projectname);

        if (0 !== $resultchk['errcode']) {
            return $resultchk;
        }
        $formcrudparms = array('entityform' => $entityform, '--generatemplate' => $generatemplate);

        $retmsggenerateform = $this->pammutils->runSymfonyCommand('pannelloamministrazione:generateformcrud', $formcrudparms);

        $retmsg = array(
            'errcode' => $retmsggenerateform['errcode'],
            'command' => $retmsggenerateform['command'],
            'message' => $retmsggenerateform['message'],
        );

        return $retmsg;
    }

    /**
     *
     * @return array<mixed>
     */
    public function checkFormCrud(string $entityform, string $projectname = "")
    {
        /* @var $fs Filesystem */
        $fs = new Filesystem();
        $srcPath = $this->apppaths->getSrcPath();
        $appPath = $srcPath;
        if (!is_writable($appPath)) {
            return array('errcode' => -1, 'message' => $appPath . ' non scrivibile');
        }

            //Look for Entities... but they should already exist...
            $entityPath = $appPath . '/Entity' . DIRECTORY_SEPARATOR . $entityform . '.php';
        if (!$fs->exists($entityPath)) {
            return array('errcode' => -1, 'message' => $entityPath . ' entity non trovata');
        }

        $formPath = $appPath . '/Form/' . $entityform . 'Type.php';
        if ($fs->exists($formPath)) {
            return array('errcode' => -1, 'message' => $formPath . ' esistente');
        }

        $controllerPath = $appPath . '/Controller' . DIRECTORY_SEPARATOR . $entityform . 'Controller.php';

        if ($fs->exists($controllerPath)) {
            return array('errcode' => -1, 'message' => $controllerPath . ' esistente');
        }

        $viewPathSrc = $this->apppaths->getTemplatePath() . DIRECTORY_SEPARATOR . $entityform;

        if ($fs->exists($viewPathSrc)) {
            return array('errcode' => -1, 'message' => $viewPathSrc . ' esistente');
        }

        return array('errcode' => 0, 'message' => 'OK');
    }

    /**
     *
     * @return array<mixed>
     */
    public function clearcache()
    {
        $cmdoutput = '';
        //$envs = array('dev', 'test', 'prod');
        $env = getenv("APP_ENV");
        if ($env === false) {
            throw new Exception("APP_ENV non definita");
        }
        $result = $this->pammutils->clearcache($env);
        $cmdoutput = $cmdoutput . $result['message'];
        if (0 !== $result['errcode']) {
            return $result;
        }
        $result['message'] = $cmdoutput;

        return $result;
    }

    /**
     *
     * @return array<mixed>
     */
    public function aggiornaSchemaDatabase(): array
    {
        $result = $this->pammutils->runSymfonyCommand('doctrine:schema:update', array('--force' => true));

        return $result;
    }
}
