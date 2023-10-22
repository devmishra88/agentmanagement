import React from "react";

import {
  Avatar,
  Container,
  CssBaseline,
  Box,
 
  Typography,
  TextField,
  // Divider,
  Switch,
  Button,
  AppBar,
  Toolbar,
  IconButton,
} from "@mui/material";
import Divider from '@mui/material/Divider';
import PropTypes from 'prop-types';
import Tabs from '@mui/material/Tabs';
import Tab from '@mui/material/Tab';
import SaveIcon from "@mui/icons-material/Save";
import MenuIcon from "@mui/icons-material/Menu";
import LockOutlinedIcon from "@mui/icons-material/LockOutlined";
import DatePicker from '@mui/lab/DatePicker';
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import FormControl from '@mui/material/FormControl';
import Select from '@mui/material/Select';
// import <Calendar></Calendar> from '@mui/x-date-pickers/Calendar';
import { DayCalendar } from "@mui/x-date-pickers/internals";
import { useState } from "react";
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Paper from '@mui/material/Paper';

//  tabPanel function


function CustomTabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          <Typography>{children}</Typography>
        </Box>
      )}
    </div>
  );
}

CustomTabPanel.propTypes = {
  children: PropTypes.node,
  index: PropTypes.number.isRequired,
  value: PropTypes.number.isRequired,
};

function a11yProps(index) {
  return {
    id: `simple-tab-${index}`,
    'aria-controls': `simple-tabpanel-${index}`,
  };
}
function createData(name, commision) {
  return { name, commision};
}
const rows = [
  createData('Danik Jagran','1%'),
  createData('Amar Ujalah', '2%'),
  createData('Hindustan', "1%"),
  createData('The Hindu',"2%"),
  createData('The Times of India',"1%"),
];
function AddAgent() {
  const [value, setValue] = React.useState(0);
  const [selectedDate, setSelectedDate] = useState(null);

  const handleDateChange = (date) => {
    setSelectedDate(date);
  };

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  const [address, setAddress] = useState({
    street: '',
    city: '',
    state: '',
    zip: '',
  });

  const handleAddressChange = (e) => {
    const { name, value } = e.target;
    setAddress({
      ...address,
      [name]: value,
    });
  };

  const [phone, setPhone] = useState('');
  const [phoneError, setPhoneError] = useState('');

  const handlePhoneChange = (e) => {
    const newPhone = e.target.value;
    setPhone(newPhone);
    // Use a regular expression to validate the phone number
    const phonePattern = /^\d{10}$/; // For a 10-digit phone number
    if (!phonePattern.test(newPhone)) {
      setPhoneError('Invalid phone number');
    } else {
      setPhoneError('');
    }
  };
  return (
    <Container
      component="main"
      maxWidth="xs"
      sx={{
        padding: '0px',
        height: "100vh",
        // overflow: "hidden",
        display: "flex",
    flexDirection: "column",
    position:"relative"
      }}
    >
      <CssBaseline />
      <AppBar position="static" sx={{
         background: `#F7F7F8`,
         position:'fixed',
         top:'0px',
         zIndex:"1000"
      }}>
          <Toolbar>
            <MenuIcon className="material-icons" sx={{
            color:'black'
            }} />
            <Avatar sx={{ m: 1, bgcolor: "primary.main" }}>
          <LockOutlinedIcon />
        </Avatar>
            <Typography variant="h6"  sx={{
                //  margin:'auto',
                 width:'50%',
                 textAlign:'center',
                 color:'black',
                 fontSize:"20px",
                 fontWeight:'500',
          
            }}>Add Agent</Typography>
          </Toolbar>
        </AppBar>
        <Box sx={{ width: '100%' }}>
      <Box sx={{ borderBottom: 1, borderColor: 'divider',  position:'fixed', top:'55px', zIndex:"1000",bgcolor:"#ffffff",width:"100%",display:"flex",justifyContent:"space-between" }}>
        <Tabs value={value} onChange={handleChange} aria-label="basic tabs example">
          <Tab label="Profile" {...a11yProps(0)} />
          <Tab label="Newspaper Commision" {...a11yProps(1)} />
         
        </Tabs>

{/* <Tabs value={value} onChange={(_, newValue) => setValue(newValue)} aria-label="basic tabs example">
  <Tab label="Profile" {...a11yProps(0)} />
  <Tab label="Newspaper Commision" {...a11yProps(1)} />
</Tabs> */}
      </Box>
      <CustomTabPanel value={value} index={0}>
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
              id="nameOfAgent"
              label="Name of Agent"
              name="nameOfAgent"
              autoComplete="off"
              autoFocus
              variant="standard"
              sx={{
                mt: 12,
              }}
            />

            <TextField
              margin="normal"
              required
              fullWidth
              id="Agency"
              label="Select Agency"
              name="selectAgency"
              autoComplete="off"
              variant="standard"
              sx={{
                my: 1,
              }}
            />
            <TextField
              margin="normal"
              fullWidth
              required
              id="areaName"
              label="Area Name"
              name="areaName"
              autoComplete="off"
              variant="standard"
              sx={{
                my: 1,
              }}
            />
            {/* Address */}
              <TextField
              required
        label="Street"
        name="street"
        value={address.street}
        onChange={handleAddressChange}
        fullWidth
        margin="normal"
        variant="standard"
      />
      <TextField
      required
        label="City"
        name="city"
        value={address.city}
        onChange={handleAddressChange}
        fullWidth
        margin="normal"
        variant="standard"
      />
      <TextField
      required
        label="State"
        name="state"
        value={address.state}
        onChange={handleAddressChange}
        fullWidth
        margin="normal"
        variant="standard"
      />
      <TextField
        label="ZIP Code"
        required
        name="zip"
        value={address.zip}
        onChange={handleAddressChange}
        fullWidth
        margin="normal"
        variant="standard"
      />

      {/* phone number */}
            <TextField
              margin="normal"
              required
              fullWidth
              id="phone"
              label="Phone"
              name="phone"
              autoComplete="off"
              variant="standard"
              sx={{
                my: 1,
              }}
              value={phone}
              onChange={handlePhoneChange}
              error={phoneError !== ''}
              helperText={phoneError}
            />
            {/* Date of Birth */}
            {/* <TextField
  label="Select date"
  value={selectedDate}
  onChange={(event) => setSelectedDate(event.target.value)}
  InputProps={{
    endAdornment: <DayCalendar />,
  }}
/>
           */}
            <TextField
              margin="normal"
              required
              fullWidth
              id="place"
              label="Place"
              name="Place"
              autoComplete="off"
              variant="standard"
              sx={{
                my: 1,
              }}
            />
           <Box>
             <Box
              display="flex"
              justifyContent="space-between"
              alignItems="center"
              width="100%"
            >
              <Typography
               required
                sx={{
                  fontSize: "17px",
                  fontWeight: "500",
                }}
              >
                Status
              </Typography>
              <Switch defaultChecked />

            </Box>
            <Divider sx={{
              px:'4px',
              height:'9px',
              // width:'2px',
            }} />
          </Box>
          <TextField
              margin="normal"
              required
              fullWidth
              id="balance"
              label="Opening Balance"
              name="openingbalance"
              autoComplete="off"
              variant="standard"
              sx={{
                my: 1,
              }}
            />
            <Box sx={{
              display:'flex',
              justifyContent:'center'
            }}>
           <Button sx={{
             width:'90px',
              m:2
           }} variant="contained">Submit</Button> 
           </Box>
            </Box>
            </Box>
      </CustomTabPanel>
      <CustomTabPanel value={value} index={1}>
      <TableContainer sx={{mt:12}} component={Paper}>
      <Table aria-label="simple table">
        <TableHead>
          <TableRow>
            <TableCell>Newspaper Name</TableCell>
            <TableCell align="right">Commision</TableCell>
            
          </TableRow>
        </TableHead>
        <TableBody>
          {rows.map((row) => (
            <TableRow
              key={row.name}
              sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
            >
              <TableCell component="th" scope="row">
                {row.name}
              </TableCell>
              <TableCell align="right">{row.commision}</TableCell>
             
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
      </CustomTabPanel>
      
    </Box>
    </Container>
  );
}

export default AddAgent;
