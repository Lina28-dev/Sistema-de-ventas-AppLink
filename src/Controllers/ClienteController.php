<?php
class ClienteController {
    private $repository;
    private $logger;
    public function __construct(ClienteRepository $repository, Logger $logger = null) {
        $this->repository = $repository;
        $this->logger = $logger ?? new Logger();
    }
    // ...métodos de la clase...
}
