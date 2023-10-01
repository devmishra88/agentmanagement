import React ,{useState} from 'react'
import  Box  from '@mui/material/Box';
import ArrowBackIosNewIcon from '@mui/icons-material/ArrowBackIosNew';
import  Card  from '@mui/material/Card';
import LockOpenIcon from '@mui/icons-material/LockOpen';
import { Button, Divider, Typography ,TextField } from '@mui/material';
import InputLabel from '@mui/material/InputLabel';

function Forgot() {
  const [mobileNumber, setMobileNumber] = useState('');
  const [hasAlphabet, setHasAlphabet] = useState(false);
  const handleMobileNumberChange = (e) => {
    const inputValue = e.target.value;
    setMobileNumber(inputValue);

   
    const hasAlphabetCharacter = /[a-zA-Z]/.test(inputValue);
    
    // Update the validation state based on the presence of alphabet characters
    setHasAlphabet(hasAlphabetCharacter);
  };
  return (
   <Box sx={{
    width:'100%',
    height:"100vh",
    backgroundColor:"#f7f7f7",
    display:"flex",
    alignItems:"center",
    justifyContent:"center"
   }}>
    <Card sx={{
        minWidth:"300px",
        // padding:"40px",

        height:'70vh'
    }}>
     <Box sx={{
        width:'100%',
        height:'40px',
        textAlign:'left',
      paddingTop:'10px',
      backgroundColor:'#F5F5F5',
    //   display:'flex',
        
        
     }}>
        <ArrowBackIosNewIcon fontSize='small' style={{
            color:'#2196f3'
        }}  />
        <LockOpenIcon fontSize='small' />
        <Typography variant='sub title' sx={{
          
            display:'inline',
            paddingLeft:'35px',
            fontSize:'20px'
           
        }} fontWeight='italic'>Forget Password</Typography>
     </Box>
     <Divider />
   <Box sx={{
    paddingTop:'70px',
    paddingBottom:'30px'
   }}>
     <LockOpenIcon sx={{
      fontSize:"70px"
      }}/>
   
   </Box>

   <Typography variant='h6' fontWeight='550'>Password Recovery</Typography>

   <Box >
   <InputLabel sx={{
           paddingTop:'60px',
           paddingLeft:'20px',
           textAlign:'left'
            }}>Mobile</InputLabel>

          <TextField
            id="forgetmobileNumber"
            variant="standard"
            size="small"
            type='tel'
            hiddenLabel
            value={mobileNumber}
            onChange={handleMobileNumberChange}
            required
   
            sx={{
               width:'80%',
               backgroundColor:'#FFFFFF',
              
           }}
           InputProps={{ disableUnderline: true }}
            ></TextField>
   
            {
                hasAlphabet && (
                   <>
                  <Typography color="red" fontSize="12px" textAlign="left" >
                       Please Match the requested format
                  </Typography>
                   </>
                )
            }
   
   </Box>

   <Button variant='contained'
   sx={{
     margin:'40px',
     marginTop:'15px',
     backgroundColor:'#2296F2'
   }}>
    RECOVER PASSWORD</Button>

    </Card>
   </Box>
  )
}

export default Forgot
