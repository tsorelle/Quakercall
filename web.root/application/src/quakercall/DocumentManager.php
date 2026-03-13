<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 10/17/2018
 * Time: 6:04 AM
 */

namespace Application\quakercall;

use Application\quakercall\db\entity\QcallDocument;
use Application\quakercall\db\repository\QcallDocumentsRepository;
// use Peanut\sys\ViewModelManager;
use JetBrains\PhpStorm\NoReturn;
use Tops\sys\TConfiguration;
use Tops\sys\TPath;
use Tops\sys\TUser;

/**
 * Not used - experimental
 */
class DocumentManager
{

    // const manageLibraryPermission='manage-document-library';
    const defaultDocumentsUri = '/documents/';
    // const defaultSearchPage = '/document-search/';
    // const defaultFormPage = '/document/';
    // const defaultAddendaUri = '/document/addenda';

    private static string $documentsUri;
    public static function getDocumentsUri() {
        if (!isset(self::$documentsUri)) {
            self::$documentsUri = TConfiguration::getValue('uri','documents',self::defaultDocumentsUri);
            if (!str_ends_with(self::$documentsUri, '/')) {
                self::$documentsUri .= '/';
            }
        }
        return self::$documentsUri;
    }


 //   private static string $searchUri ;
/*    public static function getSearchPage() {
        if (!isset(self::$searchUri)) {
            self::$searchUri = TConfiguration::getValue('documentSearch','pages',self::defaultSearchPage);
            if (substr(self::$searchUri,strlen(self::$searchUri) - 1) !== '/') {
                self::$searchUri .= '/';
            }
        }
        return self::$searchUri;
    }*/
/*
    private static string $formPage;

    public static function getFormPage() {
        if (!isset(self::$formPage)) {
            self::$formPage = TConfiguration::getValue('documentForm','pages',self::defaultFormPage);
            if (!self::$formPage) {
                self::$formPage = ViewModelManager::getVmUrl('Document','qnut-documents');
            }
        }
        return self::$formPage;
    }
*/

    /**
     * @var QcallDocumentsRepository DocumentsRepository
     */
    private QcallDocumentsRepository $documentsRepository;

    const defaultDocumentLocation = 'application/documents';

    public function __construct()
    {
        $this->documentsRepository = new QcallDocumentsRepository();
    }

    /**
     * @var DocumentManager
     */
    private static DocumentManager $instance;

    /**
     * @return DocumentManager
     */
    public static function  getInstance() : DocumentManager {
        if (!isset(self::$instance)) {
            self::$instance = new DocumentManager();
        }
        return self::$instance;
    }

    private static string $documentDir;

    /**
     * @param $args
     */
    #[NoReturn]
    public static function outputDocumentContent($args) : void
    {
        $argc = count($args);
        $download = false;
        if ($argc > 1 && strtolower($args[$argc - 1]) === 'download') {
            $download = true;
            array_pop($args);
            $argc--;
        }
        if ($argc == 0) {
            self::exitNotFound();
        }
        if (is_numeric($args[0])) {
            $document = self::getInstance()->getDocument($args[0]);
            if (empty($document)) {
                self::exitNotFound();
            }
        } else {
            $filename = array_pop($args);

            if (!str_contains($filename, '.')) {
                $filename .= '.pdf';
            }
            if (empty($args)) {
                $folder = null;
            }
            else {
                $folder = implode('/', $args);
                $folder = str_replace('+','/',$folder);
            }
            $document = self::getInstance()->getDocumentByName($filename,$folder);
        }

        if (empty($document)) {
            self::exitNotFound();
        }

        self::openDocument($document,$download);
    }

    public static function getDocumentDir($folder='',$fileName='') : string {
        if (!isset(self::$documentDir)) {
            $location = TConfiguration::getValue('location','documents',self::defaultDocumentLocation);
            self::$documentDir = TPath::fromFileRoot($location);
            if (!is_dir(self::$documentDir)) {
                mkdir(self::$documentDir, 0664, true);
            }
        }
        // $result =  TPath::joinPath(self::$documentDir, ($private ? 'private' : 'public'));
        $result =  self::$documentDir;
        if ($folder) {
            $result = TPath::joinPath($result,$folder);
        }
        if ($fileName) {
            $result = TPath::joinPath($result,$fileName);
        }
        return $result;
    }

    public function getDocumentPath($document) : string {
        return self::getDocumentDir($document->folder,$document->filename);
    }

    private static function getMimeType($ext) : string {
        // mime types  https://www.lifewire.com/file-extensions-and-mime-types-3469109

        return match (strtolower($ext)) {
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'rtf' => 'text/rtf',
            'doc', 'docx' => 'application/mssord',
            default => 'application/octet-stream',
        };
    }

    /**
     * Stream document file from the library
     * Called from routing function, depending on the CMS.
     * @param null $uri
     */
    #[NoReturn]
    public static function returnDocumentContent($uri=null) : void {
        if ($uri === null) {
            global $_SERVER;
            $uri = $_SERVER['REQUEST_URI'];
        }
        $docsUri = DocumentManager::getDocumentsUri();
        $args =  explode('/',substr($uri,strlen($docsUri)));

        self::outputDocumentContent($args);
    }

    #[NoReturn]
    public static function exitNotFound() : void
    {
        header("HTTP/1.0 404 Not Found");
        print ('Document not found in the library');
        exit;
    }

    #[NoReturn]
    public static function exitNotAuthorized() : void {
        header("HTTP/1.0 401  Unauthorized");
        print ('This document is protected. You must sign in view it.');
        exit;
    }

    #[NoReturn]
    public static function openDocument(QcallDocument $document, $download=null) : void {
        if (!TUser::getCurrent()->isAuthenticated()) {
            self::exitNotAuthorized();
        }
        $filepath = self::getDocumentDir($document->folder,$document->filename);
        if (!file_exists($filepath)) {
            self::exitNotFound();
        }

        $ext = strtolower(pathinfo($document->filename, PATHINFO_EXTENSION));
        $mimetype = self::getMimeType($ext);

        if ($download === true || $ext !== 'pdf') {
            header("Content-Disposition: attachment; filename=$document->filename;");
        }

        header("Content-Type: $mimetype");
        header('Content-Length: ' . filesize($filepath));

        $data = file_get_contents($filepath);
        print $data;
        exit;
    }

    public function getDocumentsRepository() : QcallDocumentsRepository {
        return $this->documentsRepository;
    }

/*
    public function getDocumentIndexRepository() {
        if (!isset($this->documentIndexRepository)) {
            $this->documentIndexRepository = new DocumentTextIndexRepository();
        }
        return $this->documentIndexRepository;
    }
*/

    /**
     * @param $id
     * @return bool|QcallDocument
     */
    public function getDocument($id) : bool|QcallDocument {
        return $this->documentsRepository->get($id);
    }

    /**
     * @param $filename
     * @param null $folder
     * @return bool|QcallDocument
     */
    public function getDocumentByName($filename,$folder = null) : bool|QcallDocument {
        return $this->documentsRepository->getByName($filename,$folder);
    }

    /**
     * @param QcallDocument $document
     * @param $userName
     * @return bool|object
     */
    public function updateDocument(QcallDocument $document, $userName) : bool|object
    {
        if (empty($document->id)) {
            $documentId = $this->documentsRepository->insert($document, $userName);
        } else {
            $documentId = $document->id;
            $this->documentsRepository->update($document, $userName);
        }

        return $this->documentsRepository->get($documentId);
    }

    public function checkDuplicateFiles($document): array
    {
        $filename = TPath::normalizeFileName($document->filename);
        return $this->documentsRepository->findDuplicates($filename,$document->folder,$document->id);
    }

    public function documentFileExists(QcallDocument $document) : bool {
        $filepath = self::getDocumentDir(trim($document->folder),$document->filename);
        return file_exists($filepath);
    }

    public function deleteDocument($id) : void
    {
        $this->documentsRepository->delete($id);
    }

}