<?php

namespace App\Modules\Client\Services;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Services\States;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Photos\Library\V1\PhotosLibraryClient;
use Google\Photos\Library\V1\PhotosLibraryResourceFactory;

class GooglePhotos
{
    public const SERVICE_NAME = 'google_photos';
    public const GOOGLE_PHOTOS_SCOPES = [
        'https://www.googleapis.com/auth/photoslibrary.readonly',
        'https://www.googleapis.com/auth/photoslibrary.appendonly',
        'https://www.googleapis.com/auth/photoslibrary.readonly.appcreateddata',
        'https://www.googleapis.com/auth/photoslibrary.edit.appcreateddata',
        'https://www.googleapis.com/auth/photoslibrary.sharing'
    ];

    private UserRefreshCredentials $authCredentials;

    public function __construct()
    {
        $integration = Integration::where('service', self::SERVICE_NAME)
            ->where('state_code', States::STATE_COMPLETED)
            ->first();
        $this->authCredentials = new UserRefreshCredentials(
            self::GOOGLE_PHOTOS_SCOPES,
            [
                'client_id' => $integration->key,
                'client_secret' => $integration->secret,
                'refresh_token' => $integration->refresh_token
            ]
        );
    }

    /**
     * @throws ValidationException
     * @throws ApiException
     */
    public function createAlbum(string $title): string
    {
        // Set up the Photos Library Client that interacts with the API
        $photosLibraryClient = new PhotosLibraryClient(['credentials' => $this->authCredentials]);

        $newAlbum = PhotosLibraryResourceFactory::album($title);
        $createdAlbum = $photosLibraryClient->createAlbum($newAlbum);

        return $createdAlbum->getId();
    }

    public function createPhoto(string $filePath, string $fileName, string $albumId): void
    {
        $photosLibraryClient = new PhotosLibraryClient(['credentials' => $this->authCredentials]);

        // Create a new upload request by opening the file
        // and specifying the media type (e.g. "image/png")
        $uploadToken = $photosLibraryClient->upload(file_get_contents($filePath), $fileName);
        $newMediaItems = [];
        // Create a NewMediaItem with the following components:
        // - uploadToken obtained from the previous upload request
        // - filename that will be shown to the user in Google Photos
        // - description that will be shown to the user in Google Photos
        $newMediaItems[0] = PhotosLibraryResourceFactory::newMediaItemWithDescriptionAndFileName(
            $uploadToken,
            $fileName,
            $fileName
        );

        $response = $photosLibraryClient->batchCreateMediaItems($newMediaItems);
        foreach ($response->getNewMediaItemResults() as $itemResult) {
            $mediaItem = $itemResult->getMediaItem();
            // It contains details such as the Id of the item, productUrl
            $id = $mediaItem->getId();

            $albumPosition = PhotosLibraryResourceFactory::albumPositionAfterMediaItem($id);
            $photosLibraryClient->batchCreateMediaItems(
                $newMediaItems,
                ['albumId' => $albumId, 'albumPosition' => $albumPosition]
            );
        }
    }
}