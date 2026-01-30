'use client';

import { useEffect, useState } from 'react';
import { useAuth } from '@/src/context/AuthContext';
import { ListTicketsCommand, Ticket } from '@/src/commands/ticketCommands';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import Link from 'next/link';
import { useRouter } from 'next/navigation';

export default function TicketsPage() {
  const [tickets, setTickets] = useState<Ticket[]>([]);
  const [status, setStatus] = useState('');
  const [priority, setPriority] = useState('');
  const [loading, setLoading] = useState(true);
  const { isAuthenticated, logout } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isAuthenticated) {
      router.push('/login');
      return;
    }
    loadTickets();
  }, [isAuthenticated, status, priority]);

  const loadTickets = async () => {
    setLoading(true);
    const filters: any = {};
    if (status) filters.status = status;
    if (priority) filters.priority = priority;

    const command = new ListTicketsCommand();
    const result = await command.execute(filters);
    setTickets(result.tickets);
    setLoading(false);
  };

  const getPriorityColor = (priority: string) => {
    if (priority === 'HIGH') return 'destructive';
    if (priority === 'MEDIUM') return 'default';
    return 'secondary';
  };

  const getStatusColor = (status: string) => {
    if (status === 'DONE') return 'default';
    if (status === 'IN_PROGRESS') return 'secondary';
    return 'outline';
  };

  return (
    <div className="min-h-screen p-8">
      <div className="max-w-6xl mx-auto">
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold">Mes Tickets</h1>
          <div className="flex gap-2">
            <Link href="/tickets/new">
              <Button>Nouveau ticket</Button>
            </Link>
            <Button variant="outline" onClick={logout}>
              Déconnexion
            </Button>
          </div>
        </div>

        <div className="flex gap-4 mb-6">
          <Select value={status} onValueChange={setStatus}>
            <SelectTrigger className="w-48">
              <SelectValue placeholder="Filtrer par statut" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value=" ">Tous</SelectItem>
              <SelectItem value="OPEN">Ouvert</SelectItem>
              <SelectItem value="IN_PROGRESS">En cours</SelectItem>
              <SelectItem value="DONE">Terminé</SelectItem>
            </SelectContent>
          </Select>

          <Select value={priority} onValueChange={setPriority}>
            <SelectTrigger className="w-48">
              <SelectValue placeholder="Filtrer par priorité" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value=" ">Toutes</SelectItem>
              <SelectItem value="LOW">Basse</SelectItem>
              <SelectItem value="MEDIUM">Moyenne</SelectItem>
              <SelectItem value="HIGH">Haute</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {loading ? (
          <p>Chargement...</p>
        ) : (
          <div className="grid gap-4">
            {tickets.length === 0 ? (
              <Card>
                <CardContent className="pt-6">
                  <p className="text-center text-gray-500">Aucun ticket trouvé</p>
                </CardContent>
              </Card>
            ) : (
              tickets.map((ticket) => (
                <Link key={ticket.id} href={`/tickets/${ticket.id}`}>
                  <Card className="hover:bg-gray-50 cursor-pointer">
                    <CardHeader>
                      <div className="flex justify-between items-start">
                        <CardTitle className="text-lg">{ticket.title}</CardTitle>
                        <div className="flex gap-2">
                          <Badge variant={getPriorityColor(ticket.priority)}>
                            {ticket.priority}
                          </Badge>
                          <Badge variant={getStatusColor(ticket.status)}>
                            {ticket.status}
                          </Badge>
                        </div>
                      </div>
                    </CardHeader>
                    <CardContent>
                      <p className="text-sm text-gray-600">{ticket.description.substring(0, 100)}...</p>
                      <p className="text-xs text-gray-400 mt-2">
                        {new Date(ticket.createdAt).toLocaleDateString('fr-FR')}
                      </p>
                    </CardContent>
                  </Card>
                </Link>
              ))
            )}
          </div>
        )}
      </div>
    </div>
  );
}
