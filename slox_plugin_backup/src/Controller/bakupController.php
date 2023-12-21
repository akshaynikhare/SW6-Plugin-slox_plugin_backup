<?php declare(strict_types=1);

namespace slox_plugin_backup\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Container\ContainerInterface;



/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class bakupController extends AbstractController
{

    /**
     * @Route("/slox-backup", name="frontend.slox_plugin_backup.backup", methods={"GET"})
     */
    public function downloadPluginAction()
    {
        // Path to your plugin folder
        $pluginFolder = $this->container->getParameter('kernel.project_dir') . '/custom/plugins';

        // var_dump($pluginFolder);die();
        // Create a unique zip file name
        $zipFileName = 'MyPlugin_' . date('Ymd_His') . '.zip';

        // Create a zip file
        $zip = new \ZipArchive();
        $zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Add all files from the plugin folder to the zip file
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pluginFolder));
        foreach ($files as $file) {
            if (!$files->isDot()) {
                $relativePath = substr($file->getPathname(), strlen($pluginFolder) + 1);
                $zip->addFile($file->getPathname(), $relativePath);
            }
        }

        $zip->close();

        // Send the zip file as a response
        $response = new BinaryFileResponse($zipFileName);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $zipFileName
        );

        return $response;
    }

    /**
     * @Route("/slox-list", name="frontend.slox_plugin_backup.list", methods={"GET"})
     */
    public function listPluginsAction()
    {
        // Path to your plugins folder
        $pluginsFolder = $this->container->getParameter('kernel.project_dir') . '/custom/plugins';

        // Get a list of plugin names
        $plugins = [];
        $directories = glob($pluginsFolder . '/*', GLOB_ONLYDIR);
        foreach ($directories as $directory) {
            $plugins[] = basename($directory);
        }

        // Generate HTML response
        $html = '<h1>List of Plugins</h1><ul>';
        foreach ($plugins as $plugin) {
            $html .= '<li>' . htmlspecialchars($plugin) . '</li>';
        }
        $html .= '</ul>';

        return new Response($html);
    }



}
