import api from '../api/client';

export interface LoginResult {
  success: boolean;
  token?: string;
  error?: string;
}

export class LoginCommand {
  async execute(email: string, password: string): Promise<LoginResult> {
    try {
      const response = await api.post('/login', { email, password });
      const token = response.data.data.token;
      localStorage.setItem('token', token);
      return { success: true, token };
    } catch (error: any) {
      return { 
        success: false, 
        error: error.response?.data?.meta?.error || 'Erreur de connexion' 
      };
    }
  }
}

export class RegisterCommand {
  async execute(email: string, password: string): Promise<LoginResult> {
    try {
      const response = await api.post('/register', { email, password });
      const token = response.data.data.token;
      localStorage.setItem('token', token);
      return { success: true, token };
    } catch (error: any) {
      return { 
        success: false, 
        error: error.response?.data?.meta?.error || "Erreur d'inscription" 
      };
    }
  }
}
