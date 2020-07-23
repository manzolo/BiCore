<?php

namespace Cdf\PannelloAmministrazioneBundle\Command;

use Cdf\BiCoreBundle\Entity\Colonnetabelle;
use Cdf\BiCoreBundle\Entity\Permessi;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;
use Cdf\PannelloAmministrazioneBundle\Utils\Utility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Cdf\BiCoreBundle\Utils\Entity\ModelUtils;
use function count;

class GenerateFormCommand extends Command {

    //Task / Process customized for Form Creation
    protected static $defaultName = 'pannelloamministrazione:generateformcrud';
    protected $apppaths;
    protected $em;
    protected $pammutils;
    private $generatemplate;
    private $projectname;
    private $isApi;
    private $kernel;
    private $typesMapping;

    protected function configure() {
        $this
                ->setDescription('Genera le views per il crud')
                ->setHelp('Genera le views per il crud, <br/>bi.mwb AppBundle default [--schemaupdate]<br/>')
                ->addArgument('entityform', InputArgument::REQUIRED, 'Il nome entity del form da creare')
                ->addOption('generatemplate', 't', InputOption::VALUE_OPTIONAL)
                ->addOption('projectname', 'p', InputOption::VALUE_OPTIONAL)
                ->addOption('isApi', 'a', InputOption::VALUE_OPTIONAL);
    }

    public function __construct($kernel, ProjectPath $projectpath, Utility $pammutils, EntityManagerInterface $em) {
        $this->kernel = $kernel;
        $this->apppaths = $projectpath;
        $this->pammutils = $pammutils;
        $this->em = $em;
        $this->loadTypesMapping();

        // you *must* call the parent constructor
        parent::__construct();
    }

    /**
     * Load mapping between types and loading methods
     */
    private function loadTypesMapping() {
        $this->typesMapping = array();
        $this->typesMapping['datetime'] = 'addDateTimeType';
        $this->typesMapping['double'] = 'addNumberType';
        $this->typesMapping['int'] = 'addIntegerType';
        $this->typesMapping['int64'] = 'addIntegerType';
        $this->typesMapping['void'] = 'addStringType';
    }

    /**
     * It insert main types to be used into a Form
     */
    private function insertUseOfTypes(array &$lines, $position) {
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\SubmitType;');
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\DateTimeType;');
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\NumberType;');
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\TextType;');
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\CheckBoxType;');
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\DateType;');
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\IntegerType;');
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\TextAreaType;');
        array_splice($lines, ++$position, 0, 'use Symfony\Component\Form\Extension\Core\Type\MailType;');
    }

    /**
     * Add portion of code to manage a field as datetime
     */
    private function addDateTimeType(array &$lines, $position, $attributeName) {
        array_splice($lines, ++$position, 0, "            ->add('" . $attributeName . "', DateTimeType::class, array(");
        array_splice($lines, ++$position, 0, "                  'widget' => 'single_text',");
        array_splice($lines, ++$position, 0, "                  'format' => 'dd/MM/yyyy HH:mm',");
        array_splice($lines, ++$position, 0, "                  'attr' => array('class' => 'bidatetimepicker'),");
        array_splice($lines, ++$position, 0, "                  ))");
    }

    /**
     * Add portion of code to manage a field as float/number
     */
    private function addNumberType(array &$lines, $position, $attributeName) {
        array_splice($lines, ++$position, 0, "            ->add('" . $attributeName . "',NumberType::class)");
    }

    /**
     * Add portion of code to manage a field as integer
     */
    private function addIntegerType(array &$lines, $position, $attributeName) {
        array_splice($lines, ++$position, 0, "            ->add('" . $attributeName . "',IntegerType::class)");
    }

    /**
     * Add portion of code to manage a field as string
     */
    private function addStringType(array &$lines, $position, $attributeName) {
        array_splice($lines, ++$position, 0, "            ->add('" . $attributeName . "',TextType::class)");
    }

    /**
     * Execute command in order to create the new form class
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        set_time_limit(0);
        //TODO: refactor variables
        $bundlename = 'App';
        $this->projectname = $input->getOption('projectname');
        $swaggerPath = '\\Swagger\\' . $this->projectname . '\\Model';
        $entityform = $input->getArgument('entityform');
        $modelClass = $swaggerPath . '\\Models' . $entityform;
        $controllerItem = $swaggerPath . '\\ControllersItem' . $entityform;

        $this->generatemplate = $input->getOption('generatemplate');
        $this->isApi = $input->getOption('isApi');

        //to be changed form generation in order to cover API/REST type
        $command = $this->apppaths->getConsoleExecute() . ' --env=dev' . ' make:form ' . $entityform . 'Type';
        //Append also entity class if is an ORM
        if ($this->isApi) {
            $command .= ' -n';
        } else {
            $command .= ' ' . $entityform;
        }
        $resultcrud = $this->pammutils->runCommand($command);
        if (0 == $resultcrud['errcode']) {
            $fs = new Filesystem();
            //Controller
            $controlleFile = $this->apppaths->getSrcPath() . '/Controller/' . $entityform . 'Controller.php';

            $formFile = $this->apppaths->getSrcPath() . '/Form/' . $entityform . 'Type.php';

            $lines = file($formFile, FILE_IGNORE_NEW_LINES);


            $pos1 = $this->findPosition($lines, 'use Symfony\Component\Form\AbstractType');

            //Some objects to be used
            $this->insertUseOfTypes($lines, $pos1);
            if ($this->isApi) {
                array_splice($lines, $pos1, 0, 'use ' . $modelClass . ';');
            }

            $pos2 = $this->findPosition($lines, '$builder', false);

            array_splice($lines, $pos2, 0, '        $submitparms = array('
                    . "'label' => 'Salva','attr' => array(\"class\" => \"btn-outline-primary bisubmit\", \"aria-label\" => \"Salva\"));");

            $pos3 = $this->findPosition($lines, '->add(', false);
            array_splice($lines, $pos3 + 1, 0, "            ->add('submit', SubmitType::class, \$submitparms)");

            if ($this->isApi) {
                $pos3 = $this->findPosition($lines, '->add(');
                //comment the line ->add()
                $lines[$pos3] = '//' . $lines[$pos3];
                //in this position should be added form attributes
                $modelUtil = new ModelUtils();
                $attributes = $modelUtil->getAttributes($controllerItem);
                foreach (array_reverse($attributes) as $attributeName => $attribute) {
                    $function = '';
                    if (isset($this->typesMapping[$attribute['format']])) {
                        $function = $this->typesMapping[$attribute['format']];
                    } else {
                        $function = $this->typesMapping['void'];
                    }
                    $this->$function($lines, $pos3, $attributeName);
                }
            }

            array_splice($lines, count($lines) - 3, 0, "            'parametriform' => array()");

            /* if($this->isApi) {
              $pos4 = $this->findPosition($lines, '\'parametriform\' => array()', false);
              array_splice($lines, $pos4, 0, "            'data_class' => Models".$entityform."::class,");
              } */


            file_put_contents($formFile, implode("\n", $lines));

            $code = $this->getControllerCode(str_replace('/', '\\', $bundlename), $entityform, $swaggerPath);
            $fs->dumpFile($controlleFile, $code);
            $output->writeln('<info>Creato ' . $controlleFile . '</info>');

            //Routing
            $retmsg = $this->generateFormRouting($entityform);
            //Twig template
            $this->copyTableStructureWiew($entityform);

            $this->generateFormsDefaultTableValues($entityform);
            $output->writeln('<info>' . $retmsg . '</info>');

            return 0;
        } else {
            $output->writeln('<error>' . $resultcrud['message'] . '</error>');

            return 1;
        }
    }

    private function findPosition(array $arr, String $keyword, bool $first = true) {
        $returnIndex = -1;
        foreach ($arr as $index => $string) {
            if (strpos($string, $keyword) !== FALSE) {
                $returnIndex = $index;
                if ($first)
                    break;
            }
        }
        return $returnIndex;
    }

    private function generateFormRouting($entityform) {
        //Routing del form
        $bundlename = 'App';
        $fs = new Filesystem();
        $routingFile = $this->apppaths->getSrcPath() . '/../config/routes/' . strtolower($entityform) . '.yml';

        $code = $this->getRoutingCode(str_replace('/', '', $bundlename), $entityform);
        $fs->dumpFile($routingFile, $code);

        $dest = $this->apppaths->getSrcPath() . '/../config/routes.yaml';

        $routingContext = str_replace('/', '', $bundlename) . '_' . $entityform . ':' . "\n" .
                '  resource: routes/' . strtolower($entityform) . '.yml' . "\n" .
                '  prefix: /' . $entityform . "\n\n";

        //Si fa l'append nel file routing del bundle per aggiungerci le rotte della tabella che stiamo gestendo
        $fh = file_get_contents($dest);
        if (false !== $fh) {
            file_put_contents($dest, $routingContext . $fh);
            $retmsg = 'Routing ' . $dest . " generato automaticamente da pannelloammonistrazionebundle\n\n* * * * CLEAR CACHE * * * *\n";
        } else {
            $retmsg = 'Impossibile generare il ruoting automaticamente da pannelloammonistrazionebundle\n';
        }

        return $retmsg;
    }

    private function copyTableStructureWiew($entityform) {
        $fs = new Filesystem();
        /* $publicfolder = $this->apppaths->getPublicPath();

          if (!$fs->exists($publicfolder . "/js")) {
          $fs->mkdir($publicfolder . "/js", 0777);
          }

          if (!$fs->exists($publicfolder . "/css")) {
          $fs->mkdir($publicfolder . "/css", 0777);
          } */

        $templatetablefolder = $this->apppaths->getTemplatePath() . DIRECTORY_SEPARATOR . $entityform;
        $crudfolder = $this->kernel->locateResource('@BiCoreBundle')
                . DIRECTORY_SEPARATOR . 'Resources/views/Standard/Crud';
        $tabellafolder = $this->kernel->locateResource('@BiCoreBundle')
                . DIRECTORY_SEPARATOR . 'Resources/views/Standard/Tabella';

        $fs->mirror($crudfolder, $templatetablefolder . '/Crud');
        if ($this->generatemplate) {
            $fs->mirror($tabellafolder, $templatetablefolder . '/Tabella');
        }

        //$fs->touch($publicfolder . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . $entityform . ".js");
        //$fs->touch($publicfolder . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . $entityform . ".css");
    }

    private function generateFormsDefaultTableValues($entityform) {
        //Si inserisce il record di default nella tabella permessi
        $ruoloAmm = $this->em->getRepository('BiCoreBundle:Ruoli')->findOneBy(array('superadmin' => true)); //SuperAdmin

        $newPermesso = new Permessi();
        $newPermesso->setCrud('crud');
        $newPermesso->setModulo($entityform);
        $newPermesso->setRuoli($ruoloAmm);
        $this->em->persist($newPermesso);
        $this->em->flush();

        $tabelle = new Colonnetabelle();
        $tabelle->setNometabella($entityform);
        $this->em->persist($tabelle);
        $this->em->flush();
    }

    /**
     * Return the portion of code for Controller
     */
    private function getControllerCode($bundlename, $tabella, String $swaggerPath): String {
        $code = '';
        if ($this->isApi) {
            $code = $this->getControllerCodeAPI($bundlename, $tabella, $swaggerPath);
        } else {
            $code = $this->getControllerCodeORM($bundlename, $tabella);
        }
        return $code;
    }

    /**
     *  It creates a Skeleton for a controller class that extends FiController 
     *  */
    private function getControllerCodeORM($bundlename, $tabella) {
        $codeTemplate = <<<EOF
<?php
namespace [bundle]\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Controller\FiController;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use [bundle]\Entity\[tabella];
use [bundle]\Form\[tabella]Type;
                
/**
* [tabella] controller.
*
*/

class [tabella]Controller extends FiController {

}
EOF;
        $codebundle = str_replace('[bundle]', $bundlename, $codeTemplate);
        $code = str_replace('[tabella]', $tabella, $codebundle);

        return $code;
    }

    /**
     *  It creates a Skeleton for a controller class that extends ApiController 
     *  */
    private function getControllerCodeAPI($bundlename, $tabella, String $swaggerPath) {
        $projectname = $this->projectname;
        $codeTemplate = <<<EOF
<?php
namespace [bundle]\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Controller\FiApiController;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use [swaggerPath]\Models[tabella];
use [bundle]\Form\[tabella]Type;
                
/**
* [tabella] controller.
* @var(biproject="$projectname")
*/

class [tabella]Controller extends FiApiController {

}
EOF;
        $codebundle = str_replace('[bundle]', $bundlename, $codeTemplate);
        $codebundle = str_replace('[swaggerPath]', $swaggerPath, $codebundle);
        $code = str_replace('[tabella]', $tabella, $codebundle);

        return $code;
    }

    private function getRoutingCode($bundlename, $tabella) {
        $codeTemplate = <<<'EOF'
[tabella]_container:
    path:  /
    controller: '[bundle]\Controller\[tabella]Controller::index'

[tabella]_lista:
    path:  /lista
    controller: '[bundle]\Controller\[tabella]Controller::lista'
    options:
        expose: true

[tabella]_indexdettaglio:
    path:  /indexDettaglio
    controller: '[bundle]\Controller\[tabella]Controller::indexDettaglio'
    options:
        expose: true

[tabella]_new:
    path:  /new
    controller: '[bundle]\Controller\[tabella]Controller::new'
    methods:    GET|POST

[tabella]_edit:
    path:  /{id}/edit
    controller: '[bundle]\Controller\[tabella]Controller::edit'

[tabella]_update:
    path:  /{id}/update
    controller: '[bundle]\Controller\[tabella]Controller::update'
    methods:    POST|PUT

[tabella]_aggiorna:
    path:  /{id}/{token}/aggiorna
    controller: '[bundle]\Controller\[tabella]Controller::aggiorna'
    methods:    POST|PUT
    options:
        expose: true

[tabella]_delete:
    path:  /{id}/{token}/delete
    controller: '[bundle]\Controller\[tabella]Controller::delete'
    methods:    POST|DELETE

[tabella]_deletemultiple:
    path:  /{token}/delete
    controller: '[bundle]\Controller\[tabella]Controller::delete'
    methods:    POST|DELETE

[tabella]_tabella:
    path:  /tabella
    controller: '[bundle]\Controller\[tabella]Controller::tabella'
    methods:    POST
EOF;
        $codebundle = str_replace('[bundle]', $bundlename, $codeTemplate);
        $code = str_replace('[tabella]', $tabella, $codebundle);

        return $code;
    }

}
