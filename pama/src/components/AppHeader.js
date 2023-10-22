// import applogo from "../assets/images/logo.png";
// import newlogo from "../assets/images/newlogo.png";

import { AppBar, Toolbar, Typography } from "@mui/material";

import MenuIcon from "@mui/icons-material/Menu";

function AppHeader({ ...props }) {
  const { children } = props;
  return (
    <AppBar position="static">
      <Toolbar
        sx={{
          background: `#F7F7F8`,
          color: `#000000`,
        }}
      >
        <MenuIcon
          className="material-icons"
          sx={{
            color: `#007AFF`,
          }}
        />
        {/* <img
          src={newlogo}
          alt="App Logo"
          style={{
            width: `100px`,
          }}
        /> */}
        <Typography
          variant="h6"
          sx={{
            ml: 1,
            fontSize: 18,
            fontWeight: `bold`,
          }}
        >
          {children}
        </Typography>
      </Toolbar>
    </AppBar>
  );
}

export default AppHeader;
