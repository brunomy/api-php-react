// src/api.js
const BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

async function http(path, options = {}) {
  const res = await fetch(`${BASE_URL}${path}`, {
    headers: { "Content-Type": "application/json", ...(options.headers || {}) },
    ...options,
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data?.error || res.statusText);
  return data;
}

export const api = {
  createUser: (payload) =>
    http("/api/users", { method: "POST", body: JSON.stringify(payload) }),
  listUsers: () => http("/api/users"),
  health: () => http("/api/health"),
};
