<?php

/**
 * TicketAttachment
 * Stores files attached to inbound/outbound ticket messages.
 */
class TicketAttachment {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    public function getByTicketId(int $ticketId): array {
        try {
            return $this->db->select(
                "SELECT * FROM TicketAttachments WHERE ticket_id = :ticket_id AND is_downloaded = 1 ORDER BY created_at ASC",
                ['ticket_id' => $ticketId]
            ) ?: [];
        } catch (Exception $e) {
            error_log('TicketAttachment getByTicketId error: ' . $e->getMessage());
            return [];
        }
    }

    public function getByMessageId(int $ticketMessageId): array {
        try {
            return $this->db->select(
                "SELECT * FROM TicketAttachments WHERE ticket_message_id = :tmid ORDER BY created_at ASC",
                ['tmid' => $ticketMessageId]
            ) ?: [];
        } catch (Exception $e) {
            error_log('TicketAttachment getByMessageId error: ' . $e->getMessage());
            return [];
        }
    }

    public function getByMsIds(?string $msMessageId, ?string $msAttachmentId): array|bool {
        try {
            if (empty($msMessageId) || empty($msAttachmentId)) {
                return false;
            }
            $rows = $this->db->select(
                // Graph IDs are case-sensitive; force case-sensitive comparisons
                "SELECT TOP 1 * FROM TicketAttachments
                 WHERE ms_message_id COLLATE Latin1_General_100_BIN2 = :mid
                   AND ms_attachment_id COLLATE Latin1_General_100_BIN2 = :aid
                 ORDER BY id DESC",
                ['mid' => $msMessageId, 'aid' => $msAttachmentId]
            );
            return $rows[0] ?? false;
        } catch (Exception $e) {
            error_log('TicketAttachment getByMsIds error: ' . $e->getMessage());
            return false;
        }
    }

    public function getPending(int $limit = 25): array {
        try {
            $limit = max(1, min(200, (int)$limit));
            // NOTE: TOP cannot be parameterized in SQL Server via PDO easily; inject bounded int.
            $query = "SELECT TOP {$limit} *
                      FROM TicketAttachments
                      WHERE is_downloaded = 0
                        AND (download_error IS NULL OR download_error = '')
                        AND ms_message_id IS NOT NULL
                        AND ms_attachment_id IS NOT NULL
                      ORDER BY created_at ASC, id ASC";
            return $this->db->select($query) ?: [];
        } catch (Exception $e) {
            error_log('TicketAttachment getPending error: ' . $e->getMessage());
            return [];
        }
    }

    public function countPendingByTicketId(int $ticketId): int {
        try {
            $rows = $this->db->select(
                "SELECT COUNT(*) AS c
                 FROM TicketAttachments
                 WHERE ticket_id = :ticket_id
                   AND is_downloaded = 0
                   AND (download_error IS NULL OR download_error = '')
                   AND ms_message_id IS NOT NULL
                   AND ms_attachment_id IS NOT NULL",
                ['ticket_id' => $ticketId]
            );
            return (int)($rows[0]['c'] ?? 0);
        } catch (Exception $e) {
            error_log('TicketAttachment countPendingByTicketId error: ' . $e->getMessage());
            return 0;
        }
    }

    public function getPendingForTicketId(int $ticketId, int $limit = 25): array {
        try {
            $ticketId = (int)$ticketId;
            $limit = max(1, min(200, (int)$limit));
            // NOTE: TOP cannot be parameterized in SQL Server via PDO easily; inject bounded int.
            $query = "SELECT TOP {$limit} *
                      FROM TicketAttachments
                      WHERE ticket_id = :ticket_id
                        AND is_downloaded = 0
                        AND (download_error IS NULL OR download_error = '')
                        AND ms_message_id IS NOT NULL
                        AND ms_attachment_id IS NOT NULL
                      ORDER BY created_at ASC, id ASC";
            return $this->db->select($query, ['ticket_id' => $ticketId]) ?: [];
        } catch (Exception $e) {
            error_log('TicketAttachment getPendingForTicketId error: ' . $e->getMessage());
            return [];
        }
    }

    public function create(array $data): int|false {
        try {
            $query = "INSERT INTO TicketAttachments (
                        ticket_id, ticket_message_id, ms_message_id, ms_attachment_id, content_id,
                        filename, original_filename, file_path, file_size, mime_type,
                        is_inline, is_downloaded, download_error, created_at, downloaded_at
                      ) VALUES (
                        :ticket_id, :ticket_message_id, :ms_message_id, :ms_attachment_id, :content_id,
                        :filename, :original_filename, :file_path, :file_size, :mime_type,
                        :is_inline, :is_downloaded, :download_error, GETDATE(), :downloaded_at
                      )";

            return $this->db->insert($query, [
                'ticket_id' => (int)$data['ticket_id'],
                'ticket_message_id' => $data['ticket_message_id'] ?? null,
                'ms_message_id' => $data['ms_message_id'] ?? null,
                'ms_attachment_id' => $data['ms_attachment_id'] ?? null,
                'content_id' => $data['content_id'] ?? null,
                'filename' => $data['filename'],
                'original_filename' => $data['original_filename'] ?? $data['filename'],
                'file_path' => $data['file_path'] ?? null,
                'file_size' => (int)($data['file_size'] ?? 0),
                'mime_type' => $data['mime_type'] ?? 'application/octet-stream',
                'is_inline' => !empty($data['is_inline']) ? 1 : 0,
                'is_downloaded' => !empty($data['is_downloaded']) ? 1 : 0,
                'download_error' => $data['download_error'] ?? null,
                'downloaded_at' => $data['downloaded_at'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log('TicketAttachment create error: ' . $e->getMessage());
            return false;
        }
    }

    public function markAsDownloaded(int $id, string $filePath): bool {
        try {
            $this->db->update(
                "UPDATE TicketAttachments SET file_path = :path, is_downloaded = 1, download_error = NULL, downloaded_at = GETDATE() WHERE id = :id",
                ['path' => $filePath, 'id' => $id]
            );
            return true;
        } catch (Exception $e) {
            error_log('TicketAttachment markAsDownloaded error: ' . $e->getMessage());
            return false;
        }
    }

    public function markDownloadFailed(int $id, string $error): bool {
        try {
            $this->db->update(
                "UPDATE TicketAttachments SET is_downloaded = 0, download_error = :err WHERE id = :id",
                ['err' => mb_substr($error, 0, 500), 'id' => $id]
            );
            return true;
        } catch (Exception $e) {
            error_log('TicketAttachment markDownloadFailed error: ' . $e->getMessage());
            return false;
        }
    }

    public function getSafeFilename(string $filename): string {
        $filename = trim($filename);
        $filename = $filename !== '' ? $filename : 'attachment';

        // Remove path components
        $filename = basename(str_replace('\\', '/', $filename));

        // Keep extension, sanitize base
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $base = pathinfo($filename, PATHINFO_FILENAME);

        $base = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $base);
        $base = trim($base, '._-');
        if ($base === '') {
            $base = 'attachment';
        }

        $safe = $base . ($ext ? ('.' . preg_replace('/[^a-zA-Z0-9]+/', '', $ext)) : '');

        // Ensure uniqueness
        return $safe;
    }
}
