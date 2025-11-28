const { URL } = require('url');

/**
 * Router para manejar rutas del servidor
 */
class Router {
  constructor() {
    this.routes = new Map();
  }

  /**
   * Registrar ruta
   */
  register(method, path, handler) {
    const key = `${method}:${path}`;
    this.routes.set(key, handler);
  }

  /**
   * Manejar request
   */
  async handle(req, res) {
    const url = new URL(req.url, `http://${req.headers.host}`);
    const method = req.method;
    const pathname = url.pathname;

    // Buscar ruta exacta
    const exactKey = `${method}:${pathname}`;
    if (this.routes.has(exactKey)) {
      return await this.routes.get(exactKey)(req, res, url);
    }

    // Si no se encuentra, retornar null para que el manejador de errores lo procese
    return null;
  }
}

module.exports = Router;

