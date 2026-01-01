<?php

class TicketCategory {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    public function getAll(): array {
        try {
            return $this->db->select("SELECT * FROM TicketCategories ORDER BY name") ?: [];
        } catch (Exception $e) {
            error_log('TicketCategory getAll error: ' . $e->getMessage());
            return [];
        }
    }

    public function create(string $name, int $slaHours = null): bool {
        try {
            $this->db->insert(
                "INSERT INTO TicketCategories (name, is_active, sla_hours, created_at) VALUES (:n, 1, :sla, GETDATE())",
                ['n' => $name, 'sla' => $slaHours]
            );
            return true;
        } catch (Exception $e) {
            error_log('TicketCategory create error: ' . $e->getMessage());
            return false;
        }
    }

    public function setActive(int $id, bool $active): bool {
        try {
            $this->db->update(
                "UPDATE TicketCategories SET is_active = :a WHERE id = :id",
                ['a' => $active ? 1 : 0, 'id' => $id]
            );
            return true;
        } catch (Exception $e) {
            error_log('TicketCategory setActive error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $this->db->remove("DELETE FROM TicketCategories WHERE id = :id", ['id' => $id]);
            return true;
        } catch (Exception $e) {
            error_log('TicketCategory delete error: ' . $e->getMessage());
            return false;
        }
    }
}
