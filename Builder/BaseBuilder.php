<?php

namespace Admingenerator\GeneratorBundle\Builder;

use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Config\FileLocator;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Component\HttpFoundation\ParameterBag;
use TwigGenerator\Builder\BaseBuilder as GenericBaseBuilder;
use TwigGenerator\Builder\Generator as GenericBaseGenerator;

abstract class BaseBuilder extends GenericBaseBuilder
{
    /**
     * @var \Admingenerator\GeneratorBundle\Builder\Generator    The generator.
     */
    protected $generator;

    /**
     * @var array
     */
    protected $templatesToGenerate = array();

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $variables;

    public function __construct()
    {
        parent::__construct();
        $this->variables = new ParameterBag(array());
        $this->twigFilters[] = '\\Doctrine\\Common\\Util\\Inflector::classify';
    }

    /**
     * Set files to generate
     *
     * @param array $templatesToGenerate (key => template file; value => output file name)
     */
    public function setTemplatesToGenerate(array $templatesToGenerate)
    {
        $this->templatesToGenerate = $templatesToGenerate;
    }

    /**
     * Add a file to generate
     *
     * @param string $template
     * @param string $outputName
     */
    public function addTemplateToGenerate($template, $outputName)
    {
        $this->templatesToGenerate[$template] = $outputName;
    }

    /**
     * Retrieve files to generate.
     *
     * @return array
     */
    public function getTemplatesToGenerate()
    {
        return $this->templatesToGenerate;
    }

    /**
     * Check if builder must generate multiple files
     * based on templatesToGenerate property.
     *
     * @return boolean
     */
    public function isMultiTemplatesBuilder()
    {
        $tmp = $this->getTemplatesToGenerate();

        return !empty($tmp);
    }

    /**
     * (non-PHPdoc)
     * @see \TwigGenerator\Builder\BaseBuilder::writeOnDisk()
     */
    public function writeOnDisk($outputDirectory)
    {
        if ($this->isMultiTemplatesBuilder()) {
            foreach ($this->getTemplatesToGenerate() as $templateName => $outputName) {
                $this->setOutputName($outputName);
                $this->setTemplateName($templateName);
                parent::writeOnDisk($outputDirectory);
            }
        } else {
            parent::writeOnDisk($outputDirectory);
        }
    }

    protected function getTwigEnvironment()
    {
        $locator = new TemplateLocator(new FileLocator($this->getTemplateDirs()));
        $templateNameParser = new TemplateNameParser();
        $loader = new FilesystemLoader($locator, $templateNameParser);
        $twig = new \Twig_Environment($loader, array(
            'autoescape' => false,
            'strict_variables' => true,
            'debug' => true,
            'cache' => $this->getGenerator()->getTempDir(),
        ));

        $this->loadTwigExtensions($twig);
        $this->loadTwigFilters($twig);

        return $twig;
    }

    /**
     * @return string the YamlKey
     */
    public function getYamlKey()
    {
        return $this->getSimpleClassName();
    }

    public function setVariables(array $variables)
    {
        $variables = new ParameterBag($variables);
        $this->variables = $variables;
    }

    /**
     * (non-PHPdoc)
     * @see Builder/Admingenerator\GeneratorBundle\Builder.BuilderInterface::getVariables()
     */
    public function getVariables()
    {
        return $this->variables->all();
    }

    /**
     * (non-PHPdoc)
     * @see Builder/Admingenerator\GeneratorBundle\Builder.BuilderInterface::hasVariable()
     * @param string $key
     * @return bool
     */
    public function hasVariable($key)
    {
        return $this->variables->has($key);
    }

    /**
     * (non-PHPdoc)
     * @see Builder/Admingenerator\GeneratorBundle\Builder.BuilderInterface::getVariable()
     */
    public function getVariable($key, $default = null, $deep = false)
    {
        return $this->variables->get($key, $default, $deep);
    }

    /**
     * Get model class from model param
     * @return string
     */
    public function getModelClass()
    {
        return $this->getSimpleClassName($this->getVariable('model'));
    }

    /**
     * Set the generator.
     *
     * @param \TwigGenerator\Builder\Generator $generator A generator.
     */
    public function setGenerator(GenericBaseGenerator $generator)
    {
        if (!$generator instanceof Generator) {
            throw new \LogicException(
                '$generator must be an instance of Admingenerator\GeneratorBundle\Builder\Generator, '
               .'other instances are not supported.'
            );
        }

        $this->generator = $generator;
    }

    /**
     * Return the generator.
     *
     * @return \Admingenerator\GeneratorBundle\Builder\Generator    The generator.
     */
    public function getGenerator()
    {
        return $this->generator;
    }
}
