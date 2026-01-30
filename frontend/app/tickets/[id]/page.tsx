'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { GetTicketCommand, UpdateTicketCommand, DeleteTicketCommand, Ticket } from '@/src/commands/ticketCommands';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';

export default function TicketDetailPage() {
  const params = useParams();
  const router = useRouter();
  const [ticket, setTicket] = useState<Ticket | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadTicket();
  }, []);

  const loadTicket = async () => {
    try {
      const command = new GetTicketCommand();
      const data = await command.execute(params.id as string);
      setTicket(data);
    } catch (error) {
      toast.error('Ticket introuvable');
      router.push('/tickets');
    }
    setLoading(false);
  };

  const handleStatusChange = async (newStatus: string) => {
    if (!ticket) return;

    try {
      const command = new UpdateTicketCommand();
      const updated = await command.execute(ticket.id, newStatus);
      setTicket(updated);
      toast.success('Statut mis à jour');
    } catch (error) {
      toast.error('Erreur lors de la mise à jour');
    }
  };

  const handleDelete = async () => {
    if (!ticket || !confirm('Supprimer ce ticket ?')) return;

    try {
      const command = new DeleteTicketCommand();
      await command.execute(ticket.id);
      toast.success('Ticket supprimé');
      router.push('/tickets');
    } catch (error) {
      toast.error('Erreur lors de la suppression');
    }
  };

  if (loading) return <div className="p-8">Chargement...</div>;
  if (!ticket) return null;

  return (
    <div className="min-h-screen p-8">
      <div className="max-w-4xl mx-auto">
        <Button variant="outline" onClick={() => router.back()} className="mb-4">
          ← Retour
        </Button>

        <Card>
          <CardHeader>
            <div className="flex justify-between items-start">
              <CardTitle className="text-2xl">{ticket.title}</CardTitle>
              <div className="flex gap-2">
                <Badge variant={ticket.priority === 'HIGH' ? 'destructive' : 'default'}>
                  {ticket.priority}
                </Badge>
                <Badge>{ticket.status}</Badge>
              </div>
            </div>
          </CardHeader>
          <CardContent className="space-y-6">
            <div>
              <h3 className="font-semibold mb-2">Description</h3>
              <p className="text-gray-700">{ticket.description}</p>
            </div>

            <div>
              <h3 className="font-semibold mb-2">Changer le statut</h3>
              <Select value={ticket.status} onValueChange={handleStatusChange}>
                <SelectTrigger className="w-48">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="OPEN">Ouvert</SelectItem>
                  <SelectItem value="IN_PROGRESS">En cours</SelectItem>
                  <SelectItem value="DONE">Terminé</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="pt-4 border-t">
              <Button variant="destructive" onClick={handleDelete}>
                Supprimer le ticket
              </Button>
            </div>

            <div className="text-sm text-gray-500">
              Créé le {new Date(ticket.createdAt).toLocaleString('fr-FR')}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
