<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class GoogleDriveController extends Controller
{
    public $gClient;

    function __construct()
    {

        $this->gClient = new \Google_Client();

        $this->gClient->setApplicationName('clienteWeb'); // ADD YOUR AUTH2 APPLICATION NAME (WHEN YOUR GENERATE SECRATE KEY)
        $this->gClient->setClientId('765636231676-re02f5jvqvmfqq7jc92u7g75o3usp88s.apps.googleusercontent.com');
        $this->gClient->setClientSecret('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');
        $this->gClient->setRedirectUri(route('google.login'));
        $this->gClient->setDeveloperKey('AIzaSyB4gZzHhcLcU0QfpnjDCb-Xx3ODEJS6q3w');
        $this->gClient->setScopes(array(
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive'
        ));

        $this->gClient->setAccessType("offline");

        $this->gClient->setApprovalPrompt("force");
    }

    public function googleLogin(Request $request)
    {

        $google_oauthV2 = new \Google_Service_Oauth2($this->gClient);

        // dd($request->get('code'));
        if ($request->get('code')) {

            $this->gClient->authenticate($request->get('code'));

            $request->session()->put('token', $this->gClient->getAccessToken());
        }

        if ($request->session()->get('token')) {

            $this->gClient->setAccessToken($request->session()->get('token'));
        }

        if ($this->gClient->getAccessToken()) {

            //FOR LOGGED IN USER, GET DETAILS FROM GOOGLE USING ACCES
            $user = User::find(1);

            $user->access_token = json_encode($request->session()->get('token'));

            $user->save();

            dd("Successfully authenticated");
        } else {

            // FOR GUEST USER, GET GOOGLE LOGIN URL
            $authUrl = $this->gClient->createAuthUrl();

            return redirect()->to($authUrl);
        }
    }

    public function googleDriveFilePpload()
    {
        $handle = opendir(public_path());
        // $element = file_get_contents(public_path('1/SC_00003382.pdf'));
        dd($handle);
        $service = new \Google_Service_Drive($this->gClient);

        $user = User::find(1);
        // dd($user);

        $this->gClient->setAccessToken(json_decode($user->access_token, true));

        if ($this->gClient->isAccessTokenExpired()) {

            // SAVE REFRESH TOKEN TO SOME VARIABLE
            $refreshTokenSaved = $this->gClient->getRefreshToken();

            // UPDATE ACCESS TOKEN
            $this->gClient->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

            // PASS ACCESS TOKEN TO SOME VARIABLE
            $updatedAccessToken = $this->gClient->getAccessToken();

            // APPEND REFRESH TOKEN
            $updatedAccessToken['refresh_token'] = $refreshTokenSaved;

            // SET THE NEW ACCES TOKEN
            $this->gClient->setAccessToken($updatedAccessToken);

            $user->access_token = $updatedAccessToken;

            $user->save();
        }

        $fileMetadata = new \Google_Service_Drive_DriveFile(array(
            'name' => $user->name,             // ADD YOUR GOOGLE DRIVE FOLDER NAME
            'mimeType' => 'application/vnd.google-apps.folder'
        ));
        dd($fileMetadata);
        $folder = $service->files->create($fileMetadata, array('fields' => 'id'));

        printf("Folder ID: %s\n", $folder->id);

        $file = new \Google_Service_Drive_DriveFile(array('name' => rand(10, 50) . '.jpg', 'parents' => array($folder->id)));

        $result = $service->files->create($file, array(

            'data'       => file_get_contents(public_path('SC_00003382.pdf')),   // ADD YOUR FILE PATH WHICH YOU WANT TO UPLOAD ON GOOGLE DRIVE
            'mimeType'   => 'application/octet-stream',
            'uploadType' => 'media'
        ));

        // GET URL OF UPLOADED FILE

        $url = 'https://drive.google.com/open?id=' . $result->id;

        dd($result);
    }

    public function googleDriverFileList()
    {
        $service = new \Google_Service_Drive($this->gClient);
        // dd($service);

        $user = User::find(1);
        $this->gClient->setAccessToken(json_decode($user->access_token, true));

        if ($this->gClient->isAccessTokenExpired()) {

            // SAVE REFRESH TOKEN TO SOME VARIABLE
            $refreshTokenSaved = $this->gClient->getRefreshToken();

            // UPDATE ACCESS TOKEN
            $this->gClient->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

            // PASS ACCESS TOKEN TO SOME VARIABLE
            $updatedAccessToken = $this->gClient->getAccessToken();

            // APPEND REFRESH TOKEN
            $updatedAccessToken['refresh_token'] = $refreshTokenSaved;

            // SET THE NEW ACCES TOKEN
            $this->gClient->setAccessToken($updatedAccessToken);

            $user->access_token = $updatedAccessToken;

            $user->save();
        }

        $fileMetadata = new \Google_Service_Drive_DriveFile(array(
            // 'name' => 'carpetaElkin',             // ADD YOUR GOOGLE DRIVE FOLDER NAME
            'mimeType' => 'application/vnd.google-apps.folder',
            'fields' => 'name'
        ));
        // $folder = $service->files->listFiles(array(
        //     'q'      => "mimeType='image/jpeg'",
        //     'spaces' => 'drive',
        //     'limit'  => 10,
        //     // 'pageToken' => $pageToken,
        //     'fields' => 'files(id, name)',
        // ));

        $folder = $service->files->listFiles(array(
            'q' => "name='2.jpg'",
            'fields' => 'files(id, name)',
        ));
        dd($folder);

        // $fileMetadata = new \Google_Service_Drive_DriveFile(array(
        //     // 'fields'   => 'files(id,name,mimeType)',
        //     'mimeType' => 'application/vnd.google-apps.folder'
        // ));
        // dd($fileMetadata);

        // $optParams = array(
        //     'pageSize' => 10,
        //     'fields' => 'files(id,name,mimeType)',
        //     'q' => 'mimeType = "application/vnd.google-apps.folder" and "root" in parents',
        //     'orderBy' => 'name'
        // );
        // $results = $service->files->listFiles($fileMetadata);
        // $folder = $service->files->create($fileMetadata, array('fields' => 'id'));
        // $files = $results->getFiles();
    }
}
