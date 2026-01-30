<?php

namespace App\Controller;

use App\Application\Service\TicketService;
use App\Domain\Enum\TicketPriority;
use App\Domain\Enum\TicketStatus;
use App\DTO\ApiResponseDTO;
use App\DTO\TicketCreateDTO;
use App\DTO\TicketResponseDTO;
use App\DTO\TicketUpdateDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/tickets')]
#[IsGranted('ROLE_USER')]
class TicketController extends AbstractController
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] TicketCreateDTO $dto
    ): JsonResponse {
        $user = $this->getUser();
        
        $ticket = $this->ticketService->createTicket(
            $dto->title,
            $dto->description,
            $dto->priority,
            $user
        );

        return $this->json(
            ApiResponseDTO::success([
                'ticket' => TicketResponseDTO::fromEntity($ticket)
            ]),
            201
        );
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
        
        $status = $request->query->get('status');
        $statusEnum = $status ? TicketStatus::from($status) : null;
        
        $priority = $request->query->get('priority');
        $priorityEnum = $priority ? TicketPriority::from($priority) : null;
        
        $sortBy = $request->query->get('sort', 'createdAt');
        $order = strtoupper($request->query->get('order', 'DESC'));
        $order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';

        $tickets = $this->ticketService->findUserTickets($user, $statusEnum, $priorityEnum, $page, $limit, $sortBy, $order);
        $total = $this->ticketService->countUserTickets($user, $statusEnum, $priorityEnum);

        return $this->json(
            ApiResponseDTO::success([
                'tickets' => array_map(fn($ticket) => TicketResponseDTO::fromEntity($ticket), $tickets)
            ], [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => (int) ceil($total / $limit)
            ])
        );
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $ticket = $this->ticketService->getTicketById($id, $this->getUser());

        if (!$ticket) {
            return $this->json(
                ApiResponseDTO::error('Ticket introuvable'),
                404
            );
        }

        return $this->json(
            ApiResponseDTO::success([
                'ticket' => TicketResponseDTO::fromEntity($ticket)
            ])
        );
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(string $id, #[MapRequestPayload] TicketUpdateDTO $dto): JsonResponse
    {
        $user = $this->getUser();
        $ticket = $this->ticketService->getTicketById($id, $user);

        if (!$ticket) {
            return $this->json(
                ApiResponseDTO::error('Ticket introuvable'),
                404
            );
        }

        if (!$this->ticketService->canUserModifyTicket($ticket, $user)) {
            return $this->json(
                ApiResponseDTO::error('Accès refusé'),
                403
            );
        }

        try {
            $ticket = $this->ticketService->updateTicketStatus($ticket, $dto->status);

            return $this->json(
                ApiResponseDTO::success([
                    'ticket' => TicketResponseDTO::fromEntity($ticket)
                ])
            );
        } catch (\RuntimeException $e) {
            return $this->json(
                ApiResponseDTO::error($e->getMessage()),
                400
            );
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $user = $this->getUser();
        $ticket = $this->ticketService->getTicketById($id, $user);

        if (!$ticket) {
            return $this->json(
                ApiResponseDTO::error('Ticket introuvable'),
                404
            );
        }

        if (!$this->ticketService->canUserModifyTicket($ticket, $user)) {
            return $this->json(
                ApiResponseDTO::error('Accès refusé'),
                403
            );
        }

        $this->ticketService->deleteTicket($ticket);

        return $this->json(
            ApiResponseDTO::success(['message' => 'Ticket supprimé']),
            200
        );
    }
}
