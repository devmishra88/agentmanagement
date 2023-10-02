import {
  Container,
  CssBaseline,
  Box,
  Typography,
  TextField,
  Divider,
  Card,
  Paper,
  Button,
} from "@mui/material";
import SaveIcon from '@mui/icons-material/Save';
import React from "react";
import MenuIcon from "@mui/icons-material/Menu";
import LockOutlinedIcon from "@mui/icons-material/LockOutlined";
import Switch from "@mui/material/Switch";

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
      <Box
        sx={
          {
            // width: "100%",
            // // height: "10vh",
            // backgroundColor: "#FFFFFF",
            // display: "flex",
            // alignItems: "center",
            // justifyContent: "center",
          }
        }
      >
        <Box
          sx={{
            width: "100%",
            height: "60px",
            textAlign: "left",
            // paddingTop: "10px",
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
            pt: 15,
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
              label="Phone"
              name="phone"
              autoComplete="off"
              autoFocus
              variant="standard"
            />

            <TextField
              margin="normal"
              required
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
              <Typography>status</Typography>
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
                boxShadow: " 0px 5px 10px 0px rgba(0, 0, 0, 0.5)",
                display:"flex",
                justifyContent:"flex-end",
                py:'8px',
                px:"8px",
                maxWidth:"394px",
                mx:"auto"
               
              }}
            >
              <Box sx={{
                display:"flex",
                justifyContent:"flex-end",
               
              }}>
                <Button color="primary" sx={{
                    display:"flex",
                    alignItems:"center",
                    gap:"7px",
                    width:"120px",
                    fontWeight:"700"
                   
                }} variant="contained">
<SaveIcon/>
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
