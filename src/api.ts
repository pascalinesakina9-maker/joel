// src/api.ts
import axios from "axios"

const api = axios.create({
  baseURL: "http://localhost/joel/api", // ton backend PHP
  headers: {
    "Content-Type": "application/json",
  },
})

export default api
