import React from "react";

import {
  Avatar,
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
import PropTypes from 'prop-types';
import SwipeableViews from 'react-swipeable-views';
import { useTheme } from '@mui/material/styles';
import { green } from '@mui/material/colors';

import Tabs from '@mui/material/Tabs';
import Tab from '@mui/material/Tab';

import Zoom from '@mui/material/Zoom';
import Fab from '@mui/material/Fab';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import UpIcon from '@mui/icons-material/KeyboardArrowUp';
import SaveIcon from "@mui/icons-material/Save";
import MenuIcon from "@mui/icons-material/Menu";
import LockOutlinedIcon from "@mui/icons-material/LockOutlined";
import {AdapterDateFns} from '@mui/lab/AdapterDateFns';
import {LocalizationProvider} from '@mui/lab/LocalizationProvider';
import DatePicker from '@mui/lab/DatePicker';
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import FormControl from '@mui/material/FormControl';
import Select from '@mui/material/Select';
//  tabPanel function
function TabPanel(props) {
    const { children, value, index, ...other } = props;
  
    return (
      <Typography
        component="div"
        role="tabpanel"
        hidden={value !== index}
        id={`action-tabpanel-${index}`}
        aria-labelledby={`action-tab-${index}`}
        {...other}
      >
        {value === index && <Box sx={{ p: 3 }}>{children}</Box>}
      </Typography>
    );
  }

  TabPanel.propTypes = {
    children: PropTypes.node,
    index: PropTypes.number.isRequired,
    value: PropTypes.number.isRequired,
  };
  
  function a11yProps(index) {
    return {
      id: `action-tab-${index}`,
      'aria-controls': `action-tabpanel-${index}`,
    };
  }

function AddAgent() {
  const [commision, setCommision] = React.useState('');

  const handleChange2 = (event) => {
    setCommision(event.target.value);
  };
    const theme = useTheme();
    const [value, setValue] = React.useState(0);
  
    const handleChange = (event, newValue) => {
      setValue(newValue);
    };
  
    const handleChangeIndex = (index) => {
      setValue(index);
    };
  
    const transitionDuration = {
      enter: theme.transitions.duration.enteringScreen,
      exit: theme.transitions.duration.leavingScreen,
    };
  const items =[
      {
        id:'name',
        label:'Name of Agent',
        name:'name',
      },
      {
        id:'agency',
        label:'Select Agency',
        name:'agency',
      },
      {
        id:'areaName',
        label:'Area Name',
        name:'areaName'
      },
      {
        id:'address',
        label:'Address',
        name:'address'
      },
      {
        id:'phone',
        label:'Phone',
        name:'phone'
      },
      {
        id:'place',
        label:'Place',
        name:'place'
      },
      {
        id:'balance',
        label:'Opening balance',
        name:'balance'
      }
    ];
  return (
    <Container
      component="main"
      maxWidth="xs"
      sx={{
        padding: 0,
        height: "100vh",
        // overflow: "hidden",
        display: "flex",
    flexDirection: "column",
      }}
    >
      <CssBaseline />
      <AppBar position="static" sx={{
         background: `#F7F7F8`,
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
       {/* Floating Action Button */}
        <Box
      sx={{
        bgcolor: 'background.paper',
      
        position: 'relative',
       
      }}
    >
      <AppBar position="static" color="default">
        <Tabs
          value={value}
          onChange={handleChange}
          indicatorColor="primary"
          textColor="primary"
          variant="fullWidth"
          aria-label="action tabs example"
        >
          <Tab label="Profile" {...a11yProps(0)} />
          <Tab label="Newspaper Commision" {...a11yProps(1)} />
        </Tabs>
      </AppBar>
      <SwipeableViews
        axis={theme.direction === 'rtl' ? 'x-reverse' : 'x'}
        index={value}
        onChangeIndex={handleChangeIndex}
        
      >
        <TabPanel value={value} index={0} dir={theme.direction}
       sx={{
        paddingTop: 0, // Remove top padding
        paddingBottom: 0, // Remove bottom padding
      }}
        >
        <Box
          component="form"
          noValidate
          sx={{
            paddingTop: '0px',
    paddingBottom: '0px',
            display: "flex",
            justifyContent: "center",
            flexDirection: "column",
            alignItems: "center",
          }}
        >
         {
  items.map((item) => (
    <Box
      sx={{
        paddingTop:'0px',
        position: "relative",
        width: "90%",
        // height: "80vh",
      }}
    >
      <TextField
        margin="normal"
        required
        fullWidth
        id={item.id}
        label={item.label}
        name={item.name}
        autoComplete="off"
        autoFocus
        variant="standard"
        InputProps={{ style: { padding: 0 } }} // Set padding to 0
        InputLabelProps={{ style: { padding: 0 } }}
      />
    </Box>
  ))
}

{/* <LocalizationProvider dateAdapter={AdapterDateFns}>
      <DatePicker
        label="Select Date"
        value={selectedDate}
        // onChange={handleDateChange}
        renderInput={(params) => <TextField {...params} />}
      />
    </LocalizationProvider> */}
            <Box
              display="flex"
              justifyContent="space-between"
              alignItems="center"

              width="90%"
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

       <AppBar
              position="fixed"
              sx={{ top: "auto", bottom:"-100px", background: `#F7F7F8` }}
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

          
            
          </Box>
       
        </TabPanel>
        <TabPanel value={value} index={1} dir={theme.direction}>
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


<FormControl variant="standard" sx={{ m: 1, width:"95%" }}>
        <InputLabel id="demo-simple-select-standard-label">Commision</InputLabel>
        <Select
          labelId="demo-simple-select-standard-label"
          id="demo-simple-select-standard"
          value={commision}
          onChange={handleChange2}
          label="Commision"
        >
          <MenuItem value="">
            <em>None</em>
          </MenuItem>
          <MenuItem value={1}>One</MenuItem>
          <MenuItem value={2}>Two</MenuItem>
     
        </Select>
      </FormControl>
            </Box>
        </TabPanel>
       
      </SwipeableViews>
   
    </Box>
    </Container>
  );
}

export default AddAgent;
