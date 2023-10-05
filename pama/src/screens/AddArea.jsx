import React from "react";

import {
  Container,
  CssBaseline,
  Box,
  Typography,
  TextField,
  Divider,
  Switch,
  Button,
} from "@mui/material";

import SaveIcon from "@mui/icons-material/Save";
import MenuIcon from "@mui/icons-material/Menu";
import LockOutlinedIcon from "@mui/icons-material/LockOutlined";

function AddArea() {
  return (
    <Container
      component="main"
      maxWidth="xs"
      sx={{
        padding: 0,
        height: "100vh",
        overflow: "hidden",
      }}
    >
      <CssBaseline />
      <Box>
        <Box
          sx={{
            width: "100%",
            height: "60px",
            textAlign: "left",

            backgroundColor: "#F5F5F5",
            display: "flex",
            justifyContent: "left",
            alignItems: "center",
            px: 2,
          }}
        >
          <MenuIcon
            sx={{
              color: "#52A4FC",
              fontSize: "30px",
            }}
          />
          <LockOutlinedIcon
            sx={{
              paddingLeft: "10px",
              fontSize: "40px",
            }}
          />
          <Typography
            sx={{
              marginLeft: "30px",
              fontWeight: "bold",
              fontSize: "18px",
            }}
          >
            Add Area
          </Typography>
        </Box>
        <Box
          component="form"
          noValidate
          sx={{
            pt: 5,
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
              autoFocus
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
            <Divider />
            <Box
              sx={{
                position: "fixed",
                bottom: "-3px",
                right: "0px",
                height: "50px",
                left: "0px",

                display: "flex",
                justifyContent: "flex-end",
                py: "8px",
                px: "8px",
                maxWidth: "394px",
                mx: "auto",
                backgroundColor: "#F7F7F7",
                boxShadow: "-1px -5px -2px 0px rgba(0,0,0,0.75)",
              }}
            >
              <Box
                sx={{
                  display: "flex",
                  justifyContent: "flex-end",
                  // backgroundColor:'red',
                }}
              >
                <Button
                  color="primary"
                  sx={{
                    display: "flex",
                    alignItems: "center",
                    gap: "7px",
                    width: "120px",
                    fontWeight: "700",
                    cursor: "pointer",
                  }}
                  variant="contained"
                >
                  <SaveIcon />
                  SAVE
                </Button>
              </Box>
            </Box>
          </Box>
        </Box>
      </Box>
    </Container>
  );
}

export default AddArea;
