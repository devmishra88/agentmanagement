import { Typography } from "@mui/material";

function Title({ ...props }) {

  const { children } = props;
  return (
    <Typography
    sx={{
      display: `block`,
      width: `100%`,
      textAlign: `center`,
      fontWeight: `bold`,
    }}
  >
    {children}
  </Typography>
  );
}

export default Title;
