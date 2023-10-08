import React from "react";
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
  Fab,
} from "@mui/material";
import ListIcon from "@mui/icons-material/List";
import LockOutlinedIcon from "@mui/icons-material/LockOutlined";
import RefreshIcon from "@mui/icons-material/Refresh";
import Avatar from "@mui/material/Avatar";
import Chip from "@mui/material/Chip";
import AddIcon from "@mui/icons-material/Add";

const data = [
  {
    // date:new Date(),
    // day:date.getFull
    name: "AMLIDHI",
    Droping_Point: "Govind Sarahgparisar new rajendra nagar",
  },
  {
    // date:new Date(),
    name: "AVANTI VIHAR",
    Droping_Point: "Avanti Vihar",
  },
  {
    // date:new Date(),
    name: "DUMARTARAI",
    Droping_Point: "Govind Sarahgparisar new rajendra nagar",
  },
];
function ManageArea() {
  return (
    <Container
      component="main"
      maxWidth="xs"
      sx={{
        padding: 0,
        height: "100vh",
        
        position:"relative"
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
          <ListIcon
            sx={{
              color: "#52A4FC",
              fontSize: "30px",
            }}
          />
          <LockOutlinedIcon
            sx={{
              paddingLeft: "20px",
              fontSize: "50px",
            }}
          />
          <Typography
            sx={{
              px: "70px",
              // margin:'auto',
              // textAlign:'center',
              fontSize: "22px",
              fontWeight: 400,
            }}
          >
            Area
          </Typography>
          <RefreshIcon
            sx={{
              // textAlign:'right',
              marginLeft: "60px",
              color: "#52A4FC",
              fontSize: "30px",
            }}
          />
        </Box>
        <Box
          sx={{
            // margin:'auto',
            textAlign: "center",
            py: "8px",
            fontSize: "20px",
            fontWeight: "500",
            backgroundColor: "#F0EFF4",
            height: "55px",
           
          }}
        >
          Total Area:12
        </Box>
        <Box
          sx={{
            // backgroundColor:'red'
            py: "20px",
            display: "flex",
            paddingLeft:'20px'
          }}
        >
          <Box
            sx={{
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
              position: "relative",
              marginRight: "10px",
            }}
          >
            {/*  for circle */}
            <div
              style={{
                position: "absolute",
                left: "0",
                top: "0",
              }}
            >
              <div
                style={{
                  width: "35px",
                  height: "35px",
                  background: "#1976D2",
                  color: "white",
                  fontWeight: "600",
                  borderRadius: "50%",
                  position: "relative",
                  
                }}
              >
                <p
                  style={{
                    position: "absolute",
                    top: "-8px",
                    left: "37%",
                  }}
                >
                  A
                </p>
              </div>
            </div>
            {/* for chips */}
            <div
              style={{
                height: "35px",
                width: "150px",
                background: "#DADADB",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                borderRadius: "20px",
              }}
            >
              Area:all
            </div>
          </Box>
          <Button
            variant="contained"
            sx={{
              borderRadius: "25px",
              fontSize: "18px",
              height: "35px",
              // padding:'5px'
            }}
          >
            Modify
          </Button>
        </Box>
        <Box
          sx={{
            // margin:'10px',
            // height:'180px',
            backgroundColor: "#F3EDF5",
            height: "100vh",
          }}
        >
          {data.map((item) => (
            <Box sx={{ paddingTop: "20px", paddingBottom: "0px", px: "10px" }}>
              {" "}
              <Box
                sx={{
                  height: "40px",
                  backgroundColor: "#1C4E7F",
                  borderTopLeftRadius: "5px",
                  borderTopRightRadius: "5px",
                }}
              ></Box>
              <Box
                sx={{
                  // border: "0.1px solid black",
                  py: "10px",
                  backgroundColor: "#FFFFFF",
                }}
              >
                <Typography
                  sx={{
                    padding: "20px",
                    // fontSize:'10px'
                  }}
                >
                  {item.name}
                </Typography>
                <Box
                  sx={{
                    // display:'inline-block',
                    paddingLeft: "20px",
                  }}
                >
                  <Typography>
                    <span
                      style={{
                        fontWeight: "600",
                      }}
                    >
                      {" "}
                      Droping Point:
                    </span>
                    {item.Droping_Point}
                  </Typography>
                </Box>
              </Box>
              <Box
                sx={{
                  backgroundColor: "#DADADB",
                  display: "flex",
                  justifyContent: "space-between",
                }}
              >
                <Button variant="text">Edit</Button>
                <Button variant="text">Delete</Button>
              </Box>
            </Box>
          ))}
        </Box>    
      </Box>
          {/* floating div  */}
          <div style={{
            position:"fixed",
            bottom:"20px",
            left:"32%",
            paddingLeft:"20px",
            paddingRight:"20px",
            paddingTop:"10px",
            paddingBottom:"10px",
            color:"white",
            display:"flex",
            alignItems:"center",
            justifyContent:"center",
            background:"#1976D2",
            borderRadius:"30px"
          }}>
              <AddIcon/> ADD AREA
          </div>
    </Container>
  );
}

export default ManageArea;
