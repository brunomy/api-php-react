// src/components/UserForm.jsx
import React, { useState } from "react";
import { useForm } from "react-hook-form";
import { api } from "../api";
import {
  Box,
  Card,
  CardContent,
  Typography,
  Stack,
  TextField,
  Alert,
} from "@mui/material";
import { LoadingButton } from "@mui/lab";

export default function UserForm() {
  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm();

  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState(null);
  const [errorMsg, setErrorMsg] = useState(null);

  const onSubmit = async (data) => {
    setLoading(true);
    setMessage(null);
    setErrorMsg(null);
    try {
      const res = await api.createUser(data);
      const user = res?.data ?? res;

      setMessage(`Usuário criado: ${user?.name ?? data.name} (${user?.email ?? data.email})`);
      reset();
    } catch (e) {
      setErrorMsg(e?.message || "Falha ao criar usuário");
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box sx={{ maxWidth: 480, mx: "auto", mt: 4 }}>
      <Card variant="outlined">
        <CardContent>
          <Typography variant="h6" fontWeight={700} gutterBottom>
            Criar usuário
          </Typography>

          <Box component="form" noValidate onSubmit={handleSubmit(onSubmit)}>
            <Stack spacing={2}>
              <TextField
                label="Nome"
                fullWidth
                {...register("name", { required: "Informe o nome" })}
                error={!!errors.name}
                helperText={errors.name?.message}
              />

              <TextField
                type="email"
                label="Email"
                fullWidth
                {...register("email", {
                  required: "Informe o email",
                  pattern: {
                    value: /\S+@\S+\.\S+/,
                    message: "Email inválido",
                  },
                })}
                error={!!errors.email}
                helperText={errors.email?.message}
              />

              <LoadingButton
                type="submit"
                variant="contained"
                loading={loading}
              >
                Salvar
              </LoadingButton>

              {message && <Alert severity="success">{message}</Alert>}
              {errorMsg && <Alert severity="error">{errorMsg}</Alert>}
            </Stack>
          </Box>
        </CardContent>
      </Card>
    </Box>
  );
}
