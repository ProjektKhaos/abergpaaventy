<?php
// MediaService.php – affärslogik för media i adminpanelen Ⓐ Style

class MediaService
{
    private Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function normalize_files(array $files): array
    {
        if (!isset($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $normalized = [];
        $total = count($files['name']);
        for ($i = 0; $i < $total; $i++) {
            $normalized[] = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $files['size'][$i] ?? 0,
            ];
        }

        return $normalized;
    }

    public function selected_uploads(array $files): array
    {
        return array_values(array_filter(
            $this->normalize_files($files),
            static fn (array $file): bool => ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
        ));
    }

    public function validate_image(array $file, string $label = 'Bilden'): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $label . ' kunde inte laddas upp.';
        }

        $tmp = $file['tmp_name'] ?? '';
        if (!$tmp || !is_file($tmp)) {
            return $label . ' kunde inte kontrolleras.';
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!in_array($mime, ALLOWED_MIME, true)) {
            return $label . ' har fel filtyp. Använd JPG, PNG eller WebP.';
        }

        return null;
    }

    public function upload_many(array $files, string $alt = '', string $caption = ''): ServiceResult
    {
        $uploads = $this->selected_uploads($files);
        if (!$uploads) {
            return ServiceResult::failure(['Välj minst en bild att ladda upp.']);
        }

        $errors = [];
        foreach ($uploads as $upload) {
            $error = $this->validate_image($upload);
            if ($error) {
                $errors[] = $error;
            }
        }

        if ($errors) {
            return ServiceResult::failure($errors);
        }

        $ids = [];
        foreach ($uploads as $upload) {
            $id = $this->media->upload($upload, $alt, $caption);
            if ($id) {
                $ids[] = $id;
            }
        }

        if (!$ids) {
            return ServiceResult::failure(['Inga bilder laddades upp. Kontrollera filtyp (jpg, png, webp).']);
        }

        return ServiceResult::success(count($ids) . ' bild(er) laddades upp.', ['media_ids' => $ids]);
    }
}
