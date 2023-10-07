import React from 'react'
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
  import ListIcon from '@mui/icons-material/List';
  import LockOutlinedIcon from "@mui/icons-material/LockOutlined";
  import RefreshIcon from '@mui/icons-material/Refresh';
function ManageArea() {
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
            <ListIcon  sx={{
              color: "#52A4FC",
              fontSize: "30px",
            }}/>
             <LockOutlinedIcon
            sx={{
              paddingLeft: "20px",
              fontSize: "50px",
            }}
          />
           <Typography sx={{
            // paddingLeft:'40px',
            margin:'auto',
            fontSize:'22px',
            fontWeight:400,
           }} > Area </Typography>
          <RefreshIcon sx={{
            color:'#52A4FC',
            fontSize:'30px'
          }}/>
            </Box>
          <Box sx={{
            margin:'auto'
          }}>Total Area:12</Box>

          </Box>

    </Container>
  )
}

export default ManageArea
