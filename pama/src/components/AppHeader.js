import { AppBar, Toolbar, Typography } from "@mui/material";
import { useDispatch } from "react-redux";
import { toggleMenu } from "../slices/CommonSlice";
import { IconButton } from "@mui/material";
import { MenuIcon } from "../constants";

function AppHeader({ ...props }) {
  const dispatch = useDispatch();

  const { children, spacing = 1 , applyshadow = true} = props;
  return (
    <>
      <AppBar position="fixed" sx={{ boxShadow:applyshadow ? `0px 2px 4px -1px rgba(0,0,0,0.2), 0px 4px 5px 0px rgba(0,0,0,0.14), 0px 1px 10px 0px rgba(0,0,0,0.12)`:`unset` }}>
        <Toolbar
          sx={{
            background: `#F7F7F8`,
            color: `#000000`,
          }}
        >
          <IconButton
            onClick={() =>
              dispatch(toggleMenu({ menuposition: `left`, menustatus: true }))
            }
          >
            <MenuIcon
              className="material-icons"
              sx={{
                color: `#007AFF`,
              }}
            />
          </IconButton>
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
      <Toolbar sx={{ mb: spacing }} />
    </>
  );
}

export default AppHeader;
