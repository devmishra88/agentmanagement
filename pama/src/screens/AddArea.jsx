import React from "react";

import {
  Container,
  Box,
  Typography,
  TextField,
  Switch,
  Grid,
} from "@mui/material";

import { AppHeader, AppFooter } from "../components";

function AddArea() {
  return (
    <>
      <AppHeader>Add Area</AppHeader>
      <Container maxWidth="lg">
        <Grid container mt={1} spacing={1}>
          <Box
            component="form"
            noValidate
            sx={{
              display: "flex",
              justifyContent: "center",
              flexDirection: "column",
              alignItems: "center",
            }}
          >
            <Box
              sx={{
                position: "relative",
                width: "90%",
                height: "80vh",
              }}
            >
              <TextField
                margin="normal"
                required
                fullWidth
                id="phone"
                label="Name"
                name="phone"
                autoComplete="off"
                autoFocus
                variant="standard"
              />

              <TextField
                margin="normal"
                fullWidth
                id="remarks"
                label="Remarks"
                name="remarks"
                autoComplete="off"
                variant="standard"
                sx={{
                  my: 5,
                }}
              />
              <Box
                display="flex"
                justifyContent="space-between"
                alignItems="center"
                width="100%"
              >
                <Typography
                  sx={{
                    fontSize: "17px",
                    fontWeight: "500",
                  }}
                >
                  Status
                </Typography>
                <Switch defaultChecked />
              </Box>
            </Box>
          </Box>
        </Grid>
      </Container>
      <AppFooter />
    </>
  );
}

export default AddArea;
