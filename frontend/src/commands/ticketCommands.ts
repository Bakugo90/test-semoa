import api from '../api/client';

export interface Ticket {
  id: string;
  title: string;
  description: string;
  status: string;
  priority: string;
  createdAt: string;
}

export class CreateTicketCommand {
  async execute(title: string, description: string, priority: string) {
    const response = await api.post('/tickets', { title, description, priority });
    return response.data.data.ticket;
  }
}

export class UpdateTicketCommand {
  async execute(id: string, status: string) {
    const response = await api.patch(`/tickets/${id}`, { status });
    return response.data.data.ticket;
  }
}

export class DeleteTicketCommand {
  async execute(id: string) {
    await api.delete(`/tickets/${id}`);
  }
}

export class ListTicketsCommand {
  async execute(filters: any = {}) {
    const params = new URLSearchParams(filters);
    const response = await api.get(`/tickets?${params}`);
    return {
      tickets: response.data.data.tickets,
      meta: response.data.meta
    };
  }
}

export class GetTicketCommand {
  async execute(id: string) {
    const response = await api.get(`/tickets/${id}`);
    return response.data.data.ticket;
  }
}
