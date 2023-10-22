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
  AppBar,
  Toolbar,
  IconButton,
} from "@mui/material";

import {SaveIcon} from "../constants"

function AddAgent() {
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
      <AppBar
        component="nav"
        sx={{
          background: `#F7F7F8`,
        }}
      >
        <Toolbar>
          {/* <IconButton
            color="inherit"
            aria-label="open drawer"
            edge="start"
            onClick={handleDrawerToggle}
            sx={{ mr: 2, display: { sm: 'none' } }}
          >
            <MenuIcon />
          </IconButton> */}
          <Typography
            variant="h6"
            component="div"
            sx={{ flexGrow: 1, display: { xs: "none", sm: "block" } }}
          >
            MUI
          </Typography>
          <Box sx={{ display: { xs: "none", sm: "block" } }}>
            {/* {navItems.map((item) => (
              <Button key={item} sx={{ color: '#fff' }}>
                {item}
              </Button>
            ))} */}
          </Box>
        </Toolbar>
      </AppBar>

      <Box component="main" sx={{ p: 1, pl: 0, pr: 0 }}>
        <Toolbar />
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

            <AppBar
              position="fixed"
              sx={{ top: "auto", bottom: 0, background: `#F7F7F8` }}
            >
              <Toolbar>
                <Box sx={{ flexGrow: 1 }} />
                <IconButton color="inherit">
                  {/* <SearchIcon /> */}
                </IconButton>
                <IconButton color="inherit">
                  {/* <MoreIcon /> */}
                </IconButton>

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

              </Toolbar>
            </AppBar>

            <Divider />
            
          </Box>
        </Box>
      </Box>
    </Container>
  );
}

export default AddAgent;
